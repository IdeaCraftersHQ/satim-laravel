<?php

namespace Ideacrafters\SatimLaravel;

use Illuminate\Support\ServiceProvider;
use Ideacrafters\SatimLaravel\Client\SatimClient;
use Ideacrafters\SatimLaravel\Contracts\SatimInterface;

class SatimServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/satim.php', 'satim');

        $this->app->singleton(SatimClient::class, function ($app) {
            return new SatimClient(
                apiUrl: config('satim.api_url'),
                username: config('satim.username'),
                password: config('satim.password'),
                verifySSL: config('satim.verify_ssl'),
                timeout: config('satim.timeout'),
                connectTimeout: config('satim.connect_timeout'),
            );
        });

        $this->app->bind(SatimInterface::class, function ($app) {
            return new Satim(
                client: $app->make(SatimClient::class),
                defaultLanguage: config('satim.language'),
                currency: config('satim.currency'),
                terminalId: config('satim.terminal_id'),
            );
        });
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
