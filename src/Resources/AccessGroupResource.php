<?php

namespace nizami\LaravelBiostar2\Resources;

use nizami\LaravelBiostar2\Biostar2Client;

class AccessGroupResource
{
    protected Biostar2Client $client;

    public function __construct(Biostar2Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get all access groups
     */
    public function all(): array
    {
        $response = $this->client->get('/api/access_groups');
        return $response->json()['AccessGroupCollection']['rows'] ?? [];
    }

    /**
     * Get access group by ID
     */
    public function get(string $accessGroupId): array
    {
        $response = $this->client->get("/api/access_groups/{$accessGroupId}");
        return $response->json()['AccessGroup'] ?? [];
    }

    /**
     * Create access group
     */
    public function create(array $data): array
    {
        $payload = ['AccessGroup' => $data];
        $response = $this->client->post('/api/access_groups', $payload);
        return $response->json();
    }

    /**
     * Update access group
     */
    public function update(string $accessGroupId, array $data): array
    {
        $payload = ['AccessGroup' => $data];
        $response = $this->client->put("/api/access_groups/{$accessGroupId}", $payload);
        return $response->json();
    }

    /**
     * Delete access group
     */
    public function delete(string $accessGroupId): bool
    {
        $response = $this->client->delete("/api/access_groups/{$accessGroupId}");
        return $response->successful();
    }

    /**
     * Search access groups
     */
    public function search(array $conditions = []): array
    {
        $payload = [
            'Query' => [
                'limit' => 10000,
                'conditions' => $conditions,
            ],
        ];

        $response = $this->client->post('/api/access_groups/search', $payload);
        $result = $response->json();
        
        return $result['AccessGroupCollection']['rows'] ?? [];
    }
}