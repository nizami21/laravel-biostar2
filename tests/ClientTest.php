<?php

namespace nizami\LaravelBiostar2\Tests;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use nizami\LaravelBiostar2\Biostar2Client;
use nizami\LaravelBiostar2\Exceptions\Biostar2Exception;

class ClientTest extends TestCase
{
    public function test_it_can_authenticate()
    {
        Http::fake([
            '*/api/login' => Http::response([], 200, ['bs-session-id' => 'fake-token']),
        ]);

        $client = new Biostar2Client(config('biostar2'));
        $token = $client->authenticate();

        $this->assertEquals('fake-token', $token);
        $this->assertEquals('fake-token', Cache::get('biostar2_session_' . md5(config('biostar2.base_url') . config('biostar2.login_id'))));
    }

    public function test_it_throws_exception_on_auth_failure()
    {
        Http::fake([
            '*/api/login' => Http::response(['message' => 'Invalid credentials'], 401),
        ]);

        $client = new Biostar2Client(config('biostar2'));

        $this->expectException(Biostar2Exception::class);
        $this->expectExceptionMessage('Authentication failed');

        $client->authenticate();
    }

    public function test_it_makes_authenticated_requests()
    {
        Http::fake([
            '*/api/login' => Http::response([], 200, ['bs-session-id' => 'fake-token']),
            '*/api/users/1' => Http::response(['User' => ['name' => 'John']], 200),
        ]);

        $client = new Biostar2Client(config('biostar2'));
        $response = $client->get('/api/users/1');

        $this->assertEquals('John', $response->json()['User']['name']);
        
        Http::assertSent(function ($request) {
            return $request->hasHeader('bs-session-id', 'fake-token') &&
                   $request->url() === 'https://api.biostar2.com/api/users/1';
        });
    }

    public function test_it_retries_on_401()
    {
        Http::fake([
            '*/api/login' => Http::response([], 200, ['bs-session-id' => 'new-token']),
            '*/api/users/1' => Http::sequence()
                ->push(['error' => 'Unauthorized'], 401)
                ->push(['User' => ['name' => 'John']], 200),
        ]);

        $client = new Biostar2Client(config('biostar2'));
        
        // Pre-set an old token in cache
        $cacheKey = 'biostar2_session_' . md5(config('biostar2.base_url') . config('biostar2.login_id'));
        Cache::put($cacheKey, 'old-token');

        $response = $client->get('/api/users/1');

        $this->assertEquals('John', $response->json()['User']['name']);
        
        Http::assertSentCount(3); // 1st GET (401), 1st Login (retry), 2nd GET (200)
    }
}