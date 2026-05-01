<?php

namespace nizami\LaravelBiostar2;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use nizami\LaravelBiostar2\Resources\UserResource;
use nizami\LaravelBiostar2\Resources\EventResource;
use nizami\LaravelBiostar2\Resources\CardResource;
use nizami\LaravelBiostar2\Resources\AccessGroupResource;
use nizami\LaravelBiostar2\Resources\DoorResource;
use nizami\LaravelBiostar2\Resources\DeviceResource;
use nizami\LaravelBiostar2\Resources\UserGroupResource;
use nizami\LaravelBiostar2\Exceptions\Biostar2Exception;

class Biostar2Client
{
    protected string $baseUrl;
    protected string $loginId;
    protected string $password;
    protected bool $verifySSL;
    protected int $tokenCacheDuration;
    protected ?string $sessionId = null;

    public UserResource $users;
    public EventResource $events;
    public CardResource $cards;
    public AccessGroupResource $accessGroups;
    public DoorResource $doors;
    public DeviceResource $devices;
    public UserGroupResource $userGroups;

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? '';
        $this->loginId = $config['login_id'] ?? '';
        $this->password = $config['password'] ?? '';
        $this->verifySSL = $config['verify_ssl'] ?? false;
        $this->tokenCacheDuration = $config['token_cache_duration'] ?? 3600;

        if (empty($this->baseUrl)) {
            throw new Biostar2Exception('Biostar2 base URL is not configured.');
        }

        $this->users = new UserResource($this);
        $this->events = new EventResource($this);
        $this->cards = new CardResource($this);
        $this->accessGroups = new AccessGroupResource($this);
        $this->doors = new DoorResource($this);
        $this->devices = new DeviceResource($this);
        $this->userGroups = new UserGroupResource($this);
    }

    /**
     * Authenticate and get session token
     */
    public function authenticate(): string
    {
        $cacheKey = 'biostar2_session_' . md5($this->baseUrl . $this->loginId);
        
        if ($cachedToken = Cache::get($cacheKey)) {
            $this->sessionId = $cachedToken;
            return $cachedToken;
        }

        try {
            $response = Http::withOptions(['verify' => $this->verifySSL])
                ->post(rtrim($this->baseUrl, '/') . '/api/login', [
                    'User' => [
                        'login_id' => $this->loginId,
                        'password' => $this->password,
                    ],
                ]);

            if (!$response->successful()) {
                throw new Biostar2Exception('Authentication failed: ' . $response->body());
            }

            // Check for logical error in 200 OK response
            $body = $response->json();
            if (isset($body['Response']['status']) && $body['Response']['status'] === 'fail') {
                throw new Biostar2Exception('Authentication failed: ' . ($body['Response']['message'] ?? 'Unknown error'));
            }

            $sessionId = $response->header('bs-session-id');
            
            if (!$sessionId) {
                throw new Biostar2Exception('Session ID not returned from authentication');
            }

            Cache::put($cacheKey, $sessionId, $this->tokenCacheDuration);
            $this->sessionId = $sessionId;

            return $sessionId;
        } catch (\Exception $e) {
            throw new Biostar2Exception('Authentication error: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get current session token
     */
    public function getSessionId(): string
    {
        if (!$this->sessionId) {
            return $this->authenticate();
        }
        return $this->sessionId;
    }

    /**
     * Make authenticated request with automatic retry on 401
     *
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @param bool $retry
     * @return \Illuminate\Http\Client\Response
     * @throws Biostar2Exception
     */
    public function request(string $method, string $endpoint, array $data = [], bool $retry = true): \Illuminate\Http\Client\Response
    {
        $sessionId = $this->getSessionId();
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

        try {
            $request = Http::withOptions(['verify' => $this->verifySSL])
                ->withHeaders(['bs-session-id' => $sessionId]);

            $response = match(strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'POST' => $request->post($url, $data),
                'PUT' => $request->put($url, $data),
                'DELETE' => $request->delete($url, $data),
                default => throw new Biostar2Exception("Unsupported HTTP method: {$method}"),
            };

            // Handle token expiration
            if ($response->status() === 401 && $retry) {
                $cacheKey = 'biostar2_session_' . md5($this->baseUrl . $this->loginId);
                Cache::forget($cacheKey);
                $this->sessionId = null;
                return $this->request($method, $endpoint, $data, false);
            }

            if (!$response->successful()) {
                throw new Biostar2Exception(
                    "API request failed [{$response->status()}]: " . $response->body()
                );
            }

            // Check for logical error in successful HTTP response
            $body = $response->json();
            if (isset($body['Response']['status']) && $body['Response']['status'] === 'fail') {
                throw new Biostar2Exception(
                    "API logical error [{$body['Response']['code'] ?? 'N/A'}]: " . ($body['Response']['message'] ?? 'Unknown error')
                );
            }

            return $response;
        } catch (Biostar2Exception $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new Biostar2Exception('Request error: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Convenience GET request
     */
    public function get(string $endpoint, array $query = []): \Illuminate\Http\Client\Response
    {
        return $this->request('GET', $endpoint, $query);
    }

    /**
     * Convenience POST request
     */
    public function post(string $endpoint, array $data = []): \Illuminate\Http\Client\Response
    {
        return $this->request('POST', $endpoint, $data);
    }

    /**
     * Convenience PUT request
     */
    public function put(string $endpoint, array $data = []): \Illuminate\Http\Client\Response
    {
        return $this->request('PUT', $endpoint, $data);
    }

    /**
     * Convenience DELETE request
     */
    public function delete(string $endpoint, array $data = []): \Illuminate\Http\Client\Response
    {
        return $this->request('DELETE', $endpoint, $data);
    }

    /**
     * Clear cached session
     */
    public function clearSession(): void
    {
        $cacheKey = 'biostar2_session_' . md5($this->baseUrl . $this->loginId);
        Cache::forget($cacheKey);
        $this->sessionId = null;
    }
}