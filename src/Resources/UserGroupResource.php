<?php

namespace nizami\LaravelBiostar2\Resources;

use nizami\LaravelBiostar2\Biostar2Client;

class UserGroupResource
{
    /** @var Biostar2Client */
    protected Biostar2Client $client;

    public function __construct(Biostar2Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get all user groups
     */
    public function all(int $offset = 0, int $limit = 1000): array
    {
        $response = $this->client->get('/api/user_groups', [
            'offset' => $offset,
            'limit' => $limit,
        ]);
        return $response->json()['UserGroupCollection']['rows'] ?? [];
    }

    /**
     * Get user group by ID
     */
    public function get(string $userGroupId): array
    {
        $response = $this->client->get("/api/user_groups/{$userGroupId}");
        return $response->json()['UserGroup'] ?? [];
    }

    /**
     * Create user group
     */
    public function create(array $data): array
    {
        $payload = ['UserGroup' => $data];
        $response = $this->client->post('/api/user_groups', $payload);
        return $response->json();
    }

    /**
     * Update user group
     */
    public function update(string $userGroupId, array $data): array
    {
        $payload = ['UserGroup' => $data];
        $response = $this->client->put("/api/user_groups/{$userGroupId}", $payload);
        return $response->json();
    }

    /**
     * Delete user group
     */
    public function delete(string $userGroupId): bool
    {
        $response = $this->client->delete("/api/user_groups/{$userGroupId}");
        return $response->successful();
    }

    /**
     * Search user groups
     */
    public function search(array $options = []): array
    {
        $payload = [
            'Query' => [
                'offset' => $options['offset'] ?? 0,
                'limit' => $options['limit'] ?? 1000,
                'conditions' => $options['conditions'] ?? [],
            ],
        ];

        $response = $this->client->post('/api/user_groups/search', $payload);
        return $response->json()['UserGroupCollection']['rows'] ?? [];
    }
}