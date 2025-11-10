<?php

namespace nizami\LaravelBiostar2;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use nizami\LaravelBiostar2\Resources\UserResource;
use nizami\LaravelBiostar2\Resources\EventResource;
use nizami\LaravelBiostar2\Resources\CardResource;
use nizami\LaravelBiostar2\Resources\AccessGroupResource;
use YourVendor\Biostar2\Exceptions\Biostar2Exception;

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

    public function __construct(array $config = [])
    {
        $this->baseUrl = $config['base_url'] ?? config('biostar2.base_url');
        $this->loginId = $config['login_id'] ?? config('biostar2.login_id');
        $this->password = $config['password'] ?? config('biostar2.password');
        $this->verifySSL = $config['verify_ssl'] ?? config('biostar2.verify_ssl', false);
        $this->tokenCacheDuration = $config['token_cache_duration'] ?? config('biostar2.token_cache_duration', 3600);

        $this->users = new UserResource($this);
        $this->events = new EventResource($this);
        $this->cards = new CardResource($this);
        $this->accessGroups = new AccessGroupResource($this);
    }

    /**
     * Authenticate and get session token
     */
    public function authenticate(): string
    {
        $cacheKey = 'biostar2_session_token';
        
        if ($cachedToken = Cache::get($cacheKey)) {
            $this->sessionId = $cachedToken;
            return $cachedToken;
        }

        try {
            $response = Http::withOptions(['verify' => $this->verifySSL])
                ->post("{$this->baseUrl}/api/login", [
                    'User' => [
                        'login_id' => $this->loginId,
                        'password' => $this->password,
                    ],
                ]);

            if (!$response->successful()) {
                throw new Biostar2Exception('Authentication failed: ' . $response->body());
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
     */
    public function request(string $method, string $endpoint, array $data = [], bool $retry = true)
    {
        $sessionId = $this->getSessionId();
        $url = "{$this->baseUrl}{$endpoint}";

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
                Cache::forget('biostar2_session_token');
                $this->sessionId = null;
                return $this->request($method, $endpoint, $data, false);
            }

            if (!$response->successful()) {
                throw new Biostar2Exception(
                    "API request failed [{$response->status()}]: {$response->body()}"
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
     * Convenience methods
     */
    public function get(string $endpoint, array $query = [])
    {
        return $this->request('GET', $endpoint, $query);
    }

    public function post(string $endpoint, array $data = [])
    {
        return $this->request('POST', $endpoint, $data);
    }

    public function put(string $endpoint, array $data = [])
    {
        return $this->request('PUT', $endpoint, $data);
    }

    public function delete(string $endpoint, array $data = [])
    {
        return $this->request('DELETE', $endpoint, $data);
    }

    /**
     * Clear cached session
     */
    public function clearSession(): void
    {
        Cache::forget('biostar2_session_token');
        $this->sessionId = null;
    }
}