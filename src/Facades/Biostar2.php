<?php

namespace nizami\LaravelBiostar2\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \YourVendor\Biostar2\Resources\UserResource users()
 * @method static \YourVendor\Biostar2\Resources\EventResource events()
 * @method static \YourVendor\Biostar2\Resources\CardResource cards()
 * @method static \YourVendor\Biostar2\Resources\AccessGroupResource accessGroups()
 * @method static string authenticate()
 * @method static string getSessionId()
 * @method static mixed request(string $method, string $endpoint, array $data = [])
 * @method static mixed get(string $endpoint, array $query = [])
 * @method static mixed post(string $endpoint, array $data = [])
 * @method static mixed put(string $endpoint, array $data = [])
 * @method static mixed delete(string $endpoint, array $data = [])
 * @method static void clearSession()
 *
 * @see \YourVendor\Biostar2\Biostar2Client
 */
class Biostar2 extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'biostar2';
    }
}