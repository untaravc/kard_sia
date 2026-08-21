<?php

namespace App\Services\Firestore;

class WebNotificationService
{
    protected const COLLECTION = 'web_notifications';

    protected $client;

    public function __construct(FirestoreClient $client)
    {
        $this->client = $client;
    }

    public function create(string $authType, int $authId, string $title, string $content, ?string $link = null): array
    {
        return $this->client->createDocument(self::COLLECTION, [
            'auth_type' => $authType,
            'auth_id' => $authId,
            'title' => $title,
            'content' => $content,
            'link' => $link,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    public function listForUser(string $authType, int $authId, int $limit = 20): array
    {
        $docs = $this->client->runQuery($this->userQuery($authType, $authId, $limit));

        return array_map([$this, 'mapDocument'], $docs);
    }

    public function unreadCount(string $authType, int $authId): int
    {
        return $this->client->count($this->userQuery($authType, $authId, null, true));
    }

    public function markAllRead(string $authType, int $authId): int
    {
        $docs = $this->client->runQuery($this->userQuery($authType, $authId, 300, true));
        $updated = 0;

        foreach (array_chunk($docs, 400) as $chunk) {
            $writes = [];
            foreach ($chunk as $doc) {
                $writes[] = [
                    'update' => [
                        'name' => $doc['name'],
                        'fields' => FirestoreClient::encodeFields(['is_read' => true]),
                    ],
                    'updateMask' => ['fieldPaths' => ['is_read']],
                ];
            }

            $this->client->batchWrite($writes);
            $updated += count($writes);
        }

        return $updated;
    }

    public function markRead(string $authType, int $authId, string $id): bool
    {
        $doc = $this->client->getDocument(self::COLLECTION, $id);
        if (!$doc) {
            return false;
        }

        $fields = FirestoreClient::decodeFields($doc['fields'] ?? []);
        if (($fields['auth_type'] ?? null) !== $authType || (int) ($fields['auth_id'] ?? -1) !== $authId) {
            return false;
        }

        $this->client->batchWrite([[
            'update' => [
                'name' => $doc['name'],
                'fields' => FirestoreClient::encodeFields(['is_read' => true]),
            ],
            'updateMask' => ['fieldPaths' => ['is_read']],
        ]]);

        return true;
    }

    /**
     * orderBy is only attached when listing (not for unread-only lookups
     * used by markAllRead/unreadCount) so those stay within Firestore's
     * automatic-index exemption for multi-equality filters without a sort.
     */
    protected function userQuery(string $authType, int $authId, ?int $limit = null, bool $unreadOnly = false): array
    {
        $filters = [
            ['fieldFilter' => ['field' => ['fieldPath' => 'auth_type'], 'op' => 'EQUAL', 'value' => ['stringValue' => $authType]]],
            ['fieldFilter' => ['field' => ['fieldPath' => 'auth_id'], 'op' => 'EQUAL', 'value' => ['integerValue' => (string) $authId]]],
        ];

        if ($unreadOnly) {
            $filters[] = ['fieldFilter' => ['field' => ['fieldPath' => 'is_read'], 'op' => 'EQUAL', 'value' => ['booleanValue' => false]]];
        }

        $query = [
            'from' => [['collectionId' => self::COLLECTION]],
            'where' => ['compositeFilter' => ['op' => 'AND', 'filters' => $filters]],
        ];

        if (!$unreadOnly) {
            $query['orderBy'] = [['field' => ['fieldPath' => 'created_at'], 'direction' => 'DESCENDING']];
        }

        if ($limit !== null) {
            $query['limit'] = $limit;
        }

        return $query;
    }

    protected function mapDocument(array $doc): array
    {
        $fields = FirestoreClient::decodeFields($doc['fields'] ?? []);

        return [
            'id' => FirestoreClient::shortId($doc['name']),
            'title' => $fields['title'] ?? '',
            'content' => $fields['content'] ?? '',
            'link' => $fields['link'] ?? null,
            'is_read' => (bool) ($fields['is_read'] ?? false),
            'created_at' => $fields['created_at'] ?? null,
        ];
    }
}
