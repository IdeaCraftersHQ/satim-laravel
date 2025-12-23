<?php

namespace Ideacrafters\SatimLaravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Ideacrafters\SatimLaravel\SatimServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SatimServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('satim.username', 'test_username');
        config()->set('satim.password', 'test_password');
        config()->set('satim.terminal_id', 'TEST12345');
        config()->set('satim.language', 'fr');
        config()->set('satim.currency', '012');
        config()->set('satim.api_url', 'https://test2.satim.dz/payment/rest');
        config()->set('satim.verify_ssl', true);
        config()->set('satim.timeout', 30);
        config()->set('satim.connect_timeout', 10);
    }
}
