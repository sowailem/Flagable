<?php

namespace Sowailem\Flagable;

use Illuminate\Support\ServiceProvider;
use Sowailem\Flagable\FlagService;

class FlagableServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('flag', function ($app) {
            return new FlagService();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/flagable.php', 'flagable'
        );
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/flagable.php' => config_path('flagable.php'),
            ], 'flagable-config');
        }
    }
}