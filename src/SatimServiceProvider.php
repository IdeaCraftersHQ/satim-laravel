<?php

namespace Oss\SatimLaravel;

use Illuminate\Support\ServiceProvider;

class SatimServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/satim.php', 'satim');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/satim.php' => config_path('satim.php'),
            ], 'satim-config');
        }
    }
}
