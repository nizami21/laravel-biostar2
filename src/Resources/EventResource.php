<?php

namespace nizami\LaravelBiostar2\Resources;

use Carbon\Carbon;
use nizami\LaravelBiostar2\Biostar2Client;

class EventResource
{
    protected Biostar2Client $client;

    // Event type constants
    const EVENT_ACCESS_GRANTED = 4102;
    const EVENT_ACCESS_DENIED = 4354;
    const EVENT_DOOR_OPENED = 6401;

    // Operator constants
    const OPERATOR_EQUAL = 0;
    const OPERATOR_IN = 2;
    const OPERATOR_BETWEEN = 3;

    public function __construct(Biostar2Client $client)
    {
        $this->client = $client;
    }

    /**
     * Search events with flexible conditions
     */
    public function search(array $options = []): array
    {
        $conditions = [];

        // Date range condition
        if (isset($options['start_date']) || isset($options['end_date'])) {
            $startDate = isset($options['start_date']) 
                ? Carbon::parse($options['start_date']) 
                : Carbon::now()->startOfMonth();
            
            $endDate = isset($options['end_date']) 
                ? Carbon::parse($options['end_date']) 
                : Carbon::now()->endOfMonth();

            $conditions[] = [
                'column' => 'datetime',
                'operator' => self::OPERATOR_BETWEEN,
                'values' => [
                    $this->formatDateTime($startDate, 0, 0, 0, 0),
                    $this->formatDateTime($endDate, 23, 59, 59, 999),
                ],
            ];
        }

        // Event types condition
        if (isset($options['event_types'])) {
            $conditions[] = [
                'column' => 'event_type_id.code',
                'operator' => self::OPERATOR_IN,
                'values' => $options['event_types'],
            ];
        }

        // Device IDs condition
        if (isset($options['device_ids'])) {
            $conditions[] = [
                'column' => 'device_id',
                'operator' => self::OPERATOR_IN,
                'values' => $options['device_ids'],
            ];
        }

        // User/Employee ID condition
        if (isset($options['user_id'])) {
            $conditions[] = [
                'column' => 'user_id.user_id',
                'operator' => self::OPERATOR_EQUAL,
                'values' => [$options['user_id']],
            ];
        } elseif (isset($options['user_ids'])) {
            $conditions[] = [
                'column' => 'user_id.user_id',
                'operator' => self::OPERATOR_IN,
                'values' => $options['user_ids'],
            ];
        }

        $payload = [
            'Query' => [
                'limit' => $options['limit'] ?? 100000,
                'conditions' => $conditions,
                'orders' => [
                    [
                        'column' => 'datetime',
                        'descending' => $options['descending'] ?? true,
                    ],
                ],
            ],
        ];

        $response = $this->client->post('/api/events/search', $payload);
        $events = $response->json();
        
        return $this->normalizeRows($events['EventCollection']['rows'] ?? []);
    }

    /**
     * Search events for today by device
     */
    public function searchTodayByDevice(int $deviceId, array $eventTypes = [self::EVENT_ACCESS_GRANTED]): array
    {
        return $this->search([
            'start_date' => Carbon::now()->startOfDay(),
            'end_date' => Carbon::now()->endOfDay(),
            'device_ids' => [$deviceId],
            'event_types' => $eventTypes,
        ]);
    }

    /**
     * Get access events for specific users
     */
    public function getAccessEvents(array $userIds, Carbon $startDate, Carbon $endDate): array
    {
        return $this->search([
            'user_ids' => $userIds,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'event_types' => [self::EVENT_ACCESS_GRANTED, self::EVENT_ACCESS_DENIED, self::EVENT_DOOR_OPENED],
        ]);
    }

    /**
     * Get events for specific devices and users
     */
    public function getDeviceEvents(array $deviceIds, array $userIds, Carbon $startDate, Carbon $endDate): array
    {
        return $this->search([
            'device_ids' => $deviceIds,
            'user_ids' => $userIds,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'event_types' => [self::EVENT_ACCESS_GRANTED, self::EVENT_ACCESS_DENIED, self::EVENT_DOOR_OPENED],
        ]);
    }

    /**
     * Format datetime for API
     */
    protected function formatDateTime(Carbon $date, int $hour = 0, int $minute = 0, int $second = 0, int $millisecond = 0): string
    {
        return $date->setTime($hour, $minute, $second, $millisecond * 1000)
            ->format('Y-m-d\TH:i:s.v\Z');
    }

    /**
     * Normalize rows to ensure it's always a numeric array
     */
    protected function normalizeRows($rows): array
    {
        // Handle string response
        if (is_string($rows)) {
            $rows = trim($rows);
            if ($rows === '') {
                return [];
            }
            
            $decoded = json_decode($rows, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $rows = $decoded;
            }
        }

        // Ensure it's an array
        if (!is_array($rows)) {
            return [];
        }

        // Convert to numeric array if needed
        if (array_keys($rows) !== range(0, count($rows) - 1)) {
            if (is_object($rows)) {
                $rows = (array) $rows;
            }
            
            // Check if it's a single event object
            if (isset($rows['datetime']) || isset($rows['user_id'])) {
                return [$rows];
            }
            
            return array_values((array) $rows);
        }

        return $rows;
    }

    /**
     * Group events by date and user
     */
    public function groupByDateAndUser(array $events): array
    {
        $grouped = [];
        
        foreach ($events as $event) {
            $userId = $event['user_id']['user_id'] ?? null;
            $date = Carbon::parse($event['datetime'])->format('Y-m-d');
            $key = "{$userId}_{$date}";
            
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            
            $grouped[$key][] = $event;
        }
        
        return $grouped;
    }
}