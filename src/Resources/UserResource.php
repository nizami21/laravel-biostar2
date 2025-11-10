<?php

namespace nizami\LaravelBiostar2\Resources;

use nizami\LaravelBiostar2\Biostar2Client;
use nizami\LaravelBiostar2\Resources\Exceptions\Biostar2Exception;
use Carbon\Carbon;

class UserResource
{
    protected Biostar2Client $client;

    public function __construct(Biostar2Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get next available user ID
     */
    public function getNextUserId(): string
    {
        $response = $this->client->get('/api/users/next_user_id');
        return $response->json()['User']['user_id'];
    }

    /**
     * Create a new user
     */
    public function create(array $userData): array
    {
        $defaultData = [
            'start_datetime' => '2001-01-01T00:00:00.00Z',
            'expiry_datetime' => '2030-12-31T23:59:00.00Z',
            'permission' => ['id' => '1'],
            'user_group_id' => ['id' => '1'],
        ];

        $payload = [
            'User' => array_merge($defaultData, $userData)
        ];

        $response = $this->client->post('/api/users', $payload);
        return $response->json();
    }

    /**
     * Get user by ID
     */
    public function get(string $userId): array
    {
        $response = $this->client->get("/api/users/{$userId}");
        return $response->json()['User'] ?? [];
    }

    /**
     * Update user
     */
    public function update(string $userId, array $userData): array
    {
        $payload = ['User' => $userData];
        $response = $this->client->put("/api/users/{$userId}", $payload);
        return $response->json();
    }

    /**
     * Delete user
     */
    public function delete(string $userId): bool
    {
        $response = $this->client->delete("/api/users/{$userId}");
        return $response->successful();
    }

    /**
     * Get user's access groups
     */
    public function getAccessGroups(string $userId): array
    {
        $user = $this->get($userId);
        return $user['access_groups'] ?? [];
    }

    /**
     * Update user's access groups
     */
    public function updateAccessGroups(string $userId, array $accessGroupIds): array
    {
        $currentGroups = $this->getAccessGroups($userId);
        $currentIds = array_map(fn($g) => (int)$g['id'], $currentGroups);
        
        $mergedIds = array_unique(array_merge($currentIds, $accessGroupIds));
        
        $formattedGroups = array_map(fn($id) => ['id' => $id], $mergedIds);
        
        return $this->update($userId, ['access_groups' => $formattedGroups]);
    }

    /**
     * Remove access groups from user
     */
    public function removeAccessGroups(string $userId, array $accessGroupIdsToRemove): array
    {
        $currentGroups = $this->getAccessGroups($userId);
        $currentIds = array_map(fn($g) => (int)$g['id'], $currentGroups);
        
        $remainingIds = array_diff($currentIds, $accessGroupIdsToRemove);
        
        $formattedGroups = array_map(fn($id) => ['id' => $id], $remainingIds);
        
        return $this->update($userId, ['access_groups' => $formattedGroups]);
    }

    /**
     * Set user's access groups (replace all)
     */
    public function setAccessGroups(string $userId, array $accessGroupIds): array
    {
        $formattedGroups = array_map(fn($id) => ['id' => $id], $accessGroupIds);
        return $this->update($userId, ['access_groups' => $formattedGroups]);
    }

    /**
     * Update user cards
     */
    public function updateCards(string $userId, array $cardIds): array
    {
        $formattedCards = array_map(fn($id) => ['id' => $id], $cardIds);
        return $this->update($userId, ['cards' => $formattedCards]);
    }

    /**
     * Remove all cards from user
     */
    public function removeCards(string $userId): array
    {
        return $this->update($userId, ['cards' => []]);
    }

    /**
     * Deactivate user (set expiry date)
     */
    public function deactivate(string $userId, ?Carbon $expiryDate = null): array
    {
        $expiryDate = $expiryDate ?? Carbon::now();
        
        return $this->update($userId, [
            'expiry_datetime' => $expiryDate->format('Y-m-d\TH:i:s.00\Z')
        ]);
    }

    /**
     * Activate user (set future expiry date)
     */
    public function activate(string $userId, ?Carbon $expiryDate = null): array
    {
        $expiryDate = $expiryDate ?? Carbon::parse('2030-12-31 23:59:00');
        
        return $this->update($userId, [
            'expiry_datetime' => $expiryDate->format('Y-m-d\TH:i:s.00\Z')
        ]);
    }
}