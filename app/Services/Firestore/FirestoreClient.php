<?php

namespace App\Services\Firestore;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Cache;

/**
 * Minimal Firestore REST API client (v1) using OAuth2 via the Firebase
 * service account. Deliberately avoids the official google/cloud-firestore
 * SDK, which hard-requires the ext-grpc PECL extension that this app's
 * PHP 7.2 hosting cannot assume is available.
 */
class FirestoreClient
{
    private const SCOPE = 'https://www.googleapis.com/auth/datastore';

    protected $http;
    protected $documentsPath;

    public function __construct()
    {
        $projectId = config('services.firebase.project_id');
        $databaseId = config('services.firebase.firestore_database');

        $this->documentsPath = "/v1/projects/{$projectId}/databases/{$databaseId}/documents";
        $this->http = new Client([
            'base_uri' => 'https://firestore.googleapis.com',
            'timeout' => 8,
        ]);
    }

    protected function accessToken(): string
    {
        return Cache::remember('firestore_access_token', 3300, function () {
            $credentials = new ServiceAccountCredentials(
                self::SCOPE,
                config('services.firebase.credentials_file')
            );

            $token = $credentials->fetchAuthToken();

            return $token['access_token'];
        });
    }

    protected function request(string $method, string $path, ?array $body = null)
    {
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken(),
            ],
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        $response = $this->http->request($method, $this->documentsPath . $path, $options);

        return json_decode((string) $response->getBody(), true);
    }

    public function getDocument(string $collection, string $id): ?array
    {
        try {
            return $this->request('GET', '/' . $collection . '/' . $id);
        } catch (ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $e;
        }
    }

    public function createDocument(string $collection, array $fields): array
    {
        return $this->request('POST', '/' . $collection, ['fields' => self::encodeFields($fields)]);
    }

    public function runQuery(array $structuredQuery): array
    {
        $rows = (array) $this->request('POST', ':runQuery', ['structuredQuery' => $structuredQuery]);

        $docs = [];
        foreach ($rows as $row) {
            if (isset($row['document'])) {
                $docs[] = $row['document'];
            }
        }

        return $docs;
    }

    public function count(array $structuredQuery): int
    {
        $rows = (array) $this->request('POST', ':runAggregationQuery', [
            'structuredAggregationQuery' => [
                'structuredQuery' => $structuredQuery,
                'aggregations' => [['alias' => 'count', 'count' => new \stdClass()]],
            ],
        ]);

        foreach ($rows as $row) {
            $value = data_get($row, 'result.aggregateFields.count.integerValue');
            if ($value !== null) {
                return (int) $value;
            }
        }

        return 0;
    }

    public function batchWrite(array $writes): array
    {
        if (empty($writes)) {
            return ['writeResults' => []];
        }

        return $this->request('POST', ':batchWrite', ['writes' => $writes]);
    }

    public function documentName(string $collection, string $id): string
    {
        return ltrim($this->documentsPath, '/') . '/' . $collection . '/' . $id;
    }

    public static function shortId(string $documentName): string
    {
        $parts = explode('/', $documentName);

        return end($parts);
    }

    public static function encodeFields(array $fields): array
    {
        $encoded = [];
        foreach ($fields as $key => $value) {
            $encoded[$key] = self::encodeValue($value);
        }

        return $encoded;
    }

    public static function encodeValue($value): array
    {
        if ($value === null) {
            return ['nullValue' => null];
        }
        if (is_bool($value)) {
            return ['booleanValue' => $value];
        }
        if (is_int($value)) {
            return ['integerValue' => (string) $value];
        }
        if (is_float($value)) {
            return ['doubleValue' => $value];
        }
        if ($value instanceof \DateTimeInterface) {
            // getTimestamp() is used (rather than setTimezone(), which isn't
            // part of the DateTimeInterface contract) so this works for any
            // DateTimeInterface implementation regardless of its timezone.
            $utc = new \DateTimeImmutable('@' . $value->getTimestamp());

            return ['timestampValue' => $utc->format('Y-m-d\TH:i:s\Z')];
        }

        return ['stringValue' => (string) $value];
    }

    public static function decodeFields(array $fields): array
    {
        $decoded = [];
        foreach ($fields as $key => $value) {
            $decoded[$key] = self::decodeValue($value);
        }

        return $decoded;
    }

    public static function decodeValue(array $value)
    {
        if (array_key_exists('nullValue', $value)) {
            return null;
        }
        if (array_key_exists('booleanValue', $value)) {
            return (bool) $value['booleanValue'];
        }
        if (array_key_exists('integerValue', $value)) {
            return (int) $value['integerValue'];
        }
        if (array_key_exists('doubleValue', $value)) {
            return (float) $value['doubleValue'];
        }
        if (array_key_exists('timestampValue', $value)) {
            return $value['timestampValue'];
        }
        if (array_key_exists('stringValue', $value)) {
            return $value['stringValue'];
        }

        return null;
    }
}
