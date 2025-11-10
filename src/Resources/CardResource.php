<?php

namespace nizami\LaravelBiostar2\Resources;

use nizami\LaravelBiostar2\Biostar2Client;

class CardResource
{
    protected Biostar2Client $client;

    public function __construct(Biostar2Client $client)
    {
        $this->client = $client;
    }

    /**
     * Create a new card
     */
    public function create(string $cardId, array $options = []): array
    {
        $payload = [
            'CardCollection' => [
                'rows' => [
                    [
                        'card_id' => $cardId,
                        'card_type' => [
                            'id' => $options['card_type_id'] ?? '1',
                            'name' => $options['card_type_name'] ?? '',
                            'type' => $options['card_type'] ?? '10',
                        ],
                        'wiegand_format_id' => [
                            'id' => $options['wiegand_format_id'] ?? '0',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->client->post('/api/cards', $payload);
        $result = $response->json();
        
        return $result['CardCollection']['rows'][0] ?? [];
    }

    /**
     * Get card by ID
     */
    public function get(string $cardId): array
    {
        $response = $this->client->get("/api/cards/{$cardId}");
        return $response->json()['Card'] ?? [];
    }

    /**
     * Delete card
     */
    public function delete(string $cardId): bool
    {
        $response = $this->client->delete("/api/cards/{$cardId}");
        return $response->successful();
    }

    /**
     * Assign card to user
     */
    public function assignToUser(string $userId, string $cardId): array
    {
        $payload = [
            'User' => [
                'cards' => [
                    ['id' => $cardId],
                ],
            ],
        ];

        $response = $this->client->put("/api/users/{$userId}", $payload);
        return $response->json();
    }

    /**
     * Create card and assign to user (convenience method)
     */
    public function createAndAssign(string $userId, string $cardNumber): array
    {
        $card = $this->create($cardNumber);
        $cardId = $card['id'] ?? null;

        if (!$cardId) {
            throw new \Exception('Failed to create card - no ID returned');
        }

        return $this->assignToUser($userId, $cardId);
    }

    /**
     * Remove all cards from user
     */
    public function removeFromUser(string $userId): array
    {
        $payload = [
            'User' => [
                'cards' => [],
            ],
        ];

        $response = $this->client->put("/api/users/{$userId}", $payload);
        return $response->json();
    }

    /**
     * Search cards
     */
    public function search(array $conditions = []): array
    {
        $payload = [
            'Query' => [
                'limit' => 10000,
                'conditions' => $conditions,
            ],
        ];

        $response = $this->client->post('/api/cards/search', $payload);
        $result = $response->json();
        
        return $result['CardCollection']['rows'] ?? [];
    }
}