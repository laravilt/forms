<?php

namespace Laravilt\Forms\Tests;

use Laravilt\Forms\FormsServiceProvider;
use Laravilt\Schemas\SchemasServiceProvider;
use Laravilt\Support\SupportServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Additional setup if needed
    }

    protected function getPackageProviders($app): array
    {
        return [
            SupportServiceProvider::class,
            SchemasServiceProvider::class,
            FormsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup environment for testing
        config()->set('database.default', 'testing');

        // The package routes run in the web middleware group, which encrypts cookies
        config()->set('app.key', 'base64:'.base64_encode(str_repeat('l', 32)));
    }
}
