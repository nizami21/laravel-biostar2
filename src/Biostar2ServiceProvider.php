<?php

namespace nizami\LaravelBiostar2;

use Illuminate\Support\ServiceProvider;

class Biostar2ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/biostar2.php', 'biostar2'
        );

        $this->app->singleton(Biostar2Client::class, function ($app) {
            return new Biostar2Client(config('biostar2'));
        });

        $this->app->alias(Biostar2Client::class, 'biostar2');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/biostar2.php' => config_path('biostar2.php'),
            ], 'biostar2-config');
        }
    }
}