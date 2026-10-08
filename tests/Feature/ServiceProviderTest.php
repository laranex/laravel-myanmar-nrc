<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laranex\LaravelMyanmarNRC\Console\Commands\SeedMyanmarNrcCommand;
use Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc;
use Laranex\LaravelMyanmarNRC\MyanmarNrc as Nrc;
use Laranex\LaravelMyanmarNRC\MyanmarNrcServiceProvider;
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
        ->and(ServiceProvider::pathsToPublish(null, 'laravel-myanmar-nrc'))->toHaveCount(5);
});

/**
 * Point the app at a private database directory and re-register the provider,
 * so parallel test processes never see each other's published migrations.
 */
function isolatedDatabasePath(): string
{
    $path = sys_get_temp_dir().'/laravel-myanmar-nrc-'.bin2hex(random_bytes(6));
    File::ensureDirectoryExists($path.'/migrations');
    app()->useDatabasePath($path);

    return $path;
}

it('publishes the migrations with a timestamp and reuses them on the next publish', function () {
    $path = isolatedDatabasePath();
    (new MyanmarNrcServiceProvider(app()))->boot();

    $this->artisan('vendor:publish', ['--tag' => 'laravel-myanmar-nrc-migrations', '--no-interaction' => true])->assertExitCode(0);

    $first = File::files($path.'/migrations');
    $files = ['create_nrc_states_table.php', 'create_nrc_townships_table.php', 'nrc_types_table.php'];

    expect($first)->toHaveCount(3);

    foreach ($files as $index => $file) {
        expect($first[$index]->getFilename())->toMatch('/^\d{4}_\d{2}_\d{2}_\d{6}_'.preg_quote($file, '/').'$/')
            ->and($first[$index]->getContents())->toBe(File::get(__DIR__.'/../../database/migrations/'.$file));
    }

    (new MyanmarNrcServiceProvider(app()))->boot();
    $this->artisan('vendor:publish', ['--tag' => 'laravel-myanmar-nrc-migrations', '--no-interaction' => true])->assertExitCode(0);

    expect(array_map(fn (SplFileInfo $file): string => $file->getFilename(), File::files($path.'/migrations')))
        ->toBe(array_map(fn (SplFileInfo $file): string => $file->getFilename(), $first));

    File::deleteDirectory($path);
});

it('stops loading the migrations the application has published', function () {
    $path = isolatedDatabasePath();
    File::copy(__DIR__.'/../../database/migrations/create_nrc_states_table.php', $path.'/migrations/2020_01_01_000000_create_nrc_states_table.php');

    $migrator = app('migrator');
    $paths = new ReflectionProperty($migrator, 'paths');
    $paths->setAccessible(true);
    $paths->setValue($migrator, []);

    (new MyanmarNrcServiceProvider(app()))->boot();

    File::deleteDirectory($path);

    expect(array_map(fn (string $path): string => basename($path), $migrator->paths()))
        ->toBe(['create_nrc_townships_table.php', 'nrc_types_table.php']);
});
