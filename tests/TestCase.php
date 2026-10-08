<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Tests;

use Laranex\LaravelMyanmarNRC\MyanmarNrc;
use Laranex\LaravelMyanmarNRC\MyanmarNrcServiceProvider;
use Laranex\LaravelMyanmarNRC\Repositories\JsonNrcRepository;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            MyanmarNrcServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'MyanmarNrc' => \Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('k', 32)));
        $app['config']->set('database.default', 'testing');
    }

    /**
     * Fill the NRC tables from the bundled JSON file.
     */
    protected function seedNrcTables(): void
    {
        $this->artisan('mm-nrc:seed')->assertSuccessful();
    }

    /**
     * Re-resolve the package services after changing its config at runtime.
     */
    protected function reloadNrc(): void
    {
        $this->app->forgetInstance(MyanmarNrc::class);
        $this->app->forgetInstance(JsonNrcRepository::class);
        \Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc::clearResolvedInstances();
    }
}
