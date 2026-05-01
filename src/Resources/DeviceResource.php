<?php

namespace nizami\LaravelBiostar2\Resources;

use nizami\LaravelBiostar2\Biostar2Client;

class DeviceResource
{
    /** @var Biostar2Client */
    protected Biostar2Client $client;

    public function __construct(Biostar2Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get all devices
     */
    public function all(int $offset = 0, int $limit = 1000): array
    {
        $response = $this->client->get('/api/devices', [
            'offset' => $offset,
            'limit' => $limit,
        ]);
        return $response->json()['DeviceCollection']['rows'] ?? [];
    }

    /**
     * Get device by ID
     */
    public function get(string $deviceId): array
    {
        $response = $this->client->get("/api/devices/{$deviceId}");
        return $response->json()['Device'] ?? [];
    }

    /**
     * Search devices
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

        $response = $this->client->post('/api/devices/search', $payload);
        return $response->json()['DeviceCollection']['rows'] ?? [];
    }

    /**
     * Reboot device
     */
    public function reboot(string $deviceId): bool
    {
        $response = $this->client->post("/api/devices/{$deviceId}/reboot");
        return $response->successful();
    }
}