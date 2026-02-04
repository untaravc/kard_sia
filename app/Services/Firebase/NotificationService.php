<?php

namespace App\Services\Firebase;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class NotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory())
            ->withServiceAccount(config('services.firebase.credentials_file'));

        $this->messaging = $factory->createMessaging();
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_filter($tokens));
        if (empty($tokens)) {
            return [
                'success' => true,
                'sent' => 0,
                'failures' => 0,
            ];
        }

        $message = CloudMessage::new()
            ->withNotification(FirebaseNotification::create($title, $body));

        if (!empty($data)) {
            $message = $message->withData($data);
        }

        try {
            $report = $this->messaging->sendMulticast($message, $tokens);

            $successes = method_exists($report, 'successes') ? $report->successes()->count() : null;
            $failures = method_exists($report, 'failures') ? $report->failures()->count() : null;
            $invalidTokens = method_exists($report, 'invalidTokens') ? $report->invalidTokens() : [];
            $unknownTokens = method_exists($report, 'unknownTokens') ? $report->unknownTokens() : [];

            return [
                'success' => true,
                'sent' => $successes !== null ? $successes : count($tokens),
                'failures' => $failures !== null ? $failures : 0,
                'invalid_tokens' => $invalidTokens,
                'unknown_tokens' => $unknownTokens,
            ];
        } catch (\Throwable $e) {
            // If batch endpoint fails, fall back to single sends.
            $fallback = $this->sendIndividually($tokens, $title, $body, $data);
            $fallback['error'] = $this->formatException($e);
            return $fallback;
        }
    }

    protected function sendIndividually(array $tokens, string $title, string $body, array $data = []): array
    {
        $sent = 0;
        $failures = 0;
        $errors = [];

        foreach ($tokens as $token) {
            try {
                $message = CloudMessage::fromArray([
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                ]);
                $this->messaging->send($message);
                $sent++;
            } catch (\Throwable $e) {
                $failures++;
                $errors[] = [
                    'token' => $token,
                    'error' => $this->formatException($e),
                ];
            }
        }

        return [
            'success' => $failures === 0,
            'sent' => $sent,
            'failures' => $failures,
            'errors' => $errors,
            'fallback' => true,
        ];
    }

    protected function formatException(\Throwable $e): array
    {
        $error = [
            'message' => $e->getMessage(),
            'class' => get_class($e),
        ];

        $previous = $e->getPrevious();
        if ($previous instanceof \GuzzleHttp\Exception\RequestException) {
            $response = $previous->getResponse();
            $request = $previous->getRequest();
            if ($request) {
                $error['request_uri'] = (string) $request->getUri();
                $error['request_method'] = $request->getMethod();
            }
            if ($response) {
                $error['status'] = $response->getStatusCode();
                $error['body'] = (string) $response->getBody();
            }
        }

        return $error;
    }
}
