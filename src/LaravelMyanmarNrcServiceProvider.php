<?php

namespace laranex\LaravelMyanmarNRC;

use Illuminate\Support\ServiceProvider;
use laranex\LaravelMyanmarNRC\Console\SeedMyanmarNRCCommand;

class LaravelMyanmarNrcServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'laravel-myanmar-nrc');

        if ($this->app->runningInConsole()) {
            $this->commands([
                SeedMyanmarNRCCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-myanmar-nrc.php'),
            ], 'laravel-myanmar-nrc');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/laravel-myanmar-nrc'),
            ], 'laravel-myanmar-nrc');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-myanmar-nrc');

        $this->app->singleton('laravel-myanmar-nrc', function () {
            return new LaravelMyanmarNrc;
        });
    }
}
