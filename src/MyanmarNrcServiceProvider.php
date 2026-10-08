<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Laranex\LaravelMyanmarNRC\Console\Commands\SeedMyanmarNrcCommand;
use Laranex\LaravelMyanmarNRC\Repositories\DatabaseNrcRepository;
use Laranex\LaravelMyanmarNRC\Repositories\JsonNrcRepository;

class MyanmarNrcServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/config/laravel-myanmar-nrc.php', 'laravel-myanmar-nrc');

        $this->app->singleton(DatabaseNrcRepository::class);

        $this->app->singleton(JsonNrcRepository::class, fn (Container $app): JsonNrcRepository => new JsonNrcRepository(
            JsonNrcRepository::resolvePath($this->config($app, 'json_file')),
        ));

        $this->app->singleton(MyanmarNrc::class, function (Container $app): MyanmarNrc {
            $locale = $this->config($app, 'locale', 'en');

            return new MyanmarNrc(
                database: $app->make(DatabaseNrcRepository::class),
                json: $app->make(JsonNrcRepository::class),
                locale: is_string($locale) ? $locale : 'en',
                dbDriven: (bool) $this->config($app, 'db_driven', true),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');
        $this->loadTranslationsFrom(dirname(__DIR__).'/lang', 'laravel-myanmar-nrc');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            SeedMyanmarNrcCommand::class,
        ]);

        $this->publishes([
            dirname(__DIR__).'/config/laravel-myanmar-nrc.php' => $this->app->configPath('laravel-myanmar-nrc.php'),
        ], ['laravel-myanmar-nrc', 'laravel-myanmar-nrc-config']);

        $this->publishes([
            dirname(__DIR__).'/lang' => $this->app->langPath('vendor/laravel-myanmar-nrc'),
        ], ['laravel-myanmar-nrc', 'laravel-myanmar-nrc-lang']);
    }

    private function config(Container $app, string $key, mixed $default = null): mixed
    {
        return $app->make(ConfigRepository::class)->get('laravel-myanmar-nrc.'.$key, $default);
    }
}
