<?php

namespace nizami\LaravelBiostar2\Resources;

use nizami\LaravelBiostar2\Biostar2Client;

class DoorResource
{
    /** @var Biostar2Client */
    protected Biostar2Client $client;

    public function __construct(Biostar2Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get all doors
     */
    public function all(int $offset = 0, int $limit = 1000): array
    {
        $response = $this->client->get('/api/doors', [
            'offset' => $offset,
            'limit' => $limit,
        ]);
        return $response->json()['DoorCollection']['rows'] ?? [];
    }

    /**
     * Get door by ID
     */
    public function get(string $doorId): array
    {
        $response = $this->client->get("/api/doors/{$doorId}");
        return $response->json()['Door'] ?? [];
    }

    /**
     * Search doors
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

        $response = $this->client->post('/api/doors/search', $payload);
        return $response->json()['DoorCollection']['rows'] ?? [];
    }

    /**
     * Unlock door
     */
    public function unlock(string $doorId): bool
    {
        $response = $this->client->post("/api/doors/{$doorId}/unlock");
        return $response->successful();
    }

    /**
     * Lock door
     */
    public function lock(string $doorId): bool
    {
        $response = $this->client->post("/api/doors/{$doorId}/lock");
        return $response->successful();
    }

    /**
     * Release door
     */
    public function release(string $doorId): bool
    {
        $response = $this->client->post("/api/doors/{$doorId}/release");
        return $response->successful();
    }

    /**
     * Clear alarm from door
     */
    public function clearAlarm(string $doorId): bool
    {
        $response = $this->client->post("/api/doors/{$doorId}/clear_alarm");
        return $response->successful();
    }
}