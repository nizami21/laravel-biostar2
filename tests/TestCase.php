<?php

namespace nizami\LaravelBiostar2\Tests;

use nizami\LaravelBiostar2\Biostar2ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            Biostar2ServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('biostar2.base_url', 'https://api.biostar2.com');
        config()->set('biostar2.login_id', 'admin');
        config()->set('biostar2.password', 'password');
    }
}