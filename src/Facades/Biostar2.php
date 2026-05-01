<?php

namespace nizami\LaravelBiostar2\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \nizami\LaravelBiostar2\Resources\UserResource users()
 * @method static \nizami\LaravelBiostar2\Resources\EventResource events()
 * @method static \nizami\LaravelBiostar2\Resources\CardResource cards()
 * @method static \nizami\LaravelBiostar2\Resources\AccessGroupResource accessGroups()
 * @method static \nizami\LaravelBiostar2\Resources\DoorResource doors()
 * @method static \nizami\LaravelBiostar2\Resources\DeviceResource devices()
 * @method static \nizami\LaravelBiostar2\Resources\UserGroupResource userGroups()
 * @method static string authenticate()
 * @method static string getSessionId()
 * @method static mixed request(string $method, string $endpoint, array $data = [])
 * @method static mixed get(string $endpoint, array $query = [])
 * @method static mixed post(string $endpoint, array $data = [])
 * @method static mixed put(string $endpoint, array $data = [])
 * @method static mixed delete(string $endpoint, array $data = [])
 * @method static void clearSession()
 *
 * @see \nizami\LaravelBiostar2\Biostar2Client
 */
class Biostar2 extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'biostar2';
    }
}