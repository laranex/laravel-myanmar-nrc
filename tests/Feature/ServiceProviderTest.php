<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laranex\LaravelMyanmarNRC\Console\Commands\SeedMyanmarNrcCommand;
use Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc;
use Laranex\LaravelMyanmarNRC\MyanmarNrc as Nrc;
use Laranex\LaravelMyanmarNRC\Repositories\DatabaseNrcRepository;
use Laranex\LaravelMyanmarNRC\Repositories\JsonNrcRepository;

it('merges the default config', function () {
    expect(config('laravel-myanmar-nrc'))->toBe([
        'locale' => 'en',
        'json_file' => null,
        'db_driven' => true,
    ]);
});

it('binds the parser as a singleton behind the facade and alias', function () {
    expect(app(Nrc::class))->toBe(app(Nrc::class))
        ->and(MyanmarNrc::getFacadeRoot())->toBe(app(Nrc::class))
        ->and(\MyanmarNrc::isValid('nope'))->toBeFalse()
        ->and(app(JsonNrcRepository::class))->toBe(app(JsonNrcRepository::class))
        ->and(app(JsonNrcRepository::class)->path())->toBe(JsonNrcRepository::bundledPath())
        ->and(app(DatabaseNrcRepository::class))->toBeInstanceOf(DatabaseNrcRepository::class);
});

it('points the JSON repository at the configured file', function (mixed $configured, string $expected) {
    config()->set('laravel-myanmar-nrc.json_file', $configured);
    $this->reloadNrc();

    expect(app(JsonNrcRepository::class)->path())->toBe($expected);
})->with([
    'null' => [null, JsonNrcRepository::bundledPath()],
    'legacy nrc.json' => ['nrc.json', JsonNrcRepository::bundledPath()],
    'custom path' => ['/data/nrc.json', '/data/nrc.json'],
]);

it('runs the package migrations', function () {
    expect(Schema::hasTable('nrc_states'))->toBeTrue()
        ->and(Schema::hasTable('nrc_townships'))->toBeTrue()
        ->and(Schema::hasTable('nrc_types'))->toBeTrue()
        ->and(Schema::hasColumns('nrc_townships', ['id', 'nrc_state_id', 'code', 'code_mm', 'name', 'name_mm']))->toBeTrue();
});

it('registers the seed command', function () {
    expect(Artisan::all())->toHaveKey('mm-nrc:seed')
        ->and(Artisan::all()['mm-nrc:seed'])->toBeInstanceOf(SeedMyanmarNrcCommand::class);
});

it('loads the translations in both languages', function () {
    expect(trans('laravel-myanmar-nrc::validation.invalid'))->toBe('The :attribute is not valid.')
        ->and(trans('laravel-myanmar-nrc::validation.invalid', [], 'mm'))->toBe('မှတ်ပုံတင်သည် အကျုံးမဝင်ပါ။');
});

it('publishes the config and translations under their tags', function () {
    $root = realpath(__DIR__.'/../..');

    expect(ServiceProvider::pathsToPublish(null, 'laravel-myanmar-nrc-config'))
        ->toBe([$root.'/config/laravel-myanmar-nrc.php' => config_path('laravel-myanmar-nrc.php')])
        ->and(ServiceProvider::pathsToPublish(null, 'laravel-myanmar-nrc-lang'))
        ->toBe([$root.'/lang' => lang_path('vendor/laravel-myanmar-nrc')])
        ->and(ServiceProvider::pathsToPublish(null, 'laravel-myanmar-nrc'))->toHaveCount(2);
});
