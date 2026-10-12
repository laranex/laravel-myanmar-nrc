<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc;
use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Repositories\DatabaseNrcRepository;
use Laranex\LaravelMyanmarNRC\Rules\MyanmarNRC as NrcRule;

beforeEach(function (): void {
    $this->seedNrcTables();
});

/**
 * Count the queries a callback runs.
 */
function countQueries(Closure $callback): int
{
    $count = 0;
    DB::listen(function () use (&$count): void {
        $count++;
    });

    $callback();

    return $count;
}

it('loads the tables once for any number of validations', function () {
    $queries = countQueries(function (): void {
        foreach (range(1, 20) as $attempt) {
            expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue()
                ->and(MyanmarNrc::isValid('12-1-1-123456'))->toBeFalse()
                ->and(validator(['nrc' => '13-331-2-987654'], ['nrc' => new NrcRule])->passes())->toBeTrue()
                ->and(MyanmarNrc::parse('12-284-1-123456'))->toBe('12/DAGAYA(N)123456');
        }
    });

    expect($queries)->toBe(3);
});

it('hydrates the cached rows as models', function () {
    $repository = app(DatabaseNrcRepository::class);

    expect($repository->state(12))->toBeInstanceOf(State::class)->code->toBe(12)->name->toBe('YANGON')->exists->toBeTrue()
        ->and($repository->township(284))->toBeInstanceOf(Township::class)->nrc_state_id->toBe(12)->code->toBe('DAGAYA')
        ->and($repository->type(1))->code->toBe('N')
        ->and($repository->state(99))->toBeNull()
        ->and($repository->township(9999))->toBeNull()
        ->and($repository->type(9))->toBeNull();
});

it('shares the data with other processes through the cache store, forever by default', function () {
    MyanmarNrc::isValid('12-284-1-123456');

    expect(Cache::get('laravel-myanmar-nrc'))->toBeArray()->toHaveKeys(['states', 'townships', 'types']);

    $this->reloadNrc();

    expect(countQueries(fn () => expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue()))->toBe(0);
});

it('uses the configured store, key and ttl', function () {
    config()->set('cache.stores.nrc', ['driver' => 'array']);
    config()->set('laravel-myanmar-nrc.cache', ['enabled' => true, 'store' => 'nrc', 'key' => 'nrc-data', 'ttl' => 60]);
    $this->reloadNrc();

    MyanmarNrc::isValid('12-284-1-123456');

    expect(Cache::store('nrc')->get('nrc-data'))->toBeArray()
        ->and(Cache::get('laravel-myanmar-nrc'))->toBeNull();

    $this->travel(61)->seconds();

    expect(Cache::store('nrc')->get('nrc-data'))->toBeNull();
});

it('keeps the data in memory only when the cache store is disabled', function () {
    config()->set('laravel-myanmar-nrc.cache.enabled', false);
    $this->reloadNrc();

    expect(countQueries(function (): void {
        MyanmarNrc::isValid('12-284-1-123456');
        MyanmarNrc::isValid('12-284-1-123456');
    }))->toBe(3)
        ->and(Cache::get('laravel-myanmar-nrc'))->toBeNull();
});

it('reads the tables again after the cache is cleared', function () {
    expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue();

    Township::query()->whereKey(284)->delete();

    expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue();

    $this->artisan('mm-nrc:clear-cache')
        ->expectsOutputToContain('The NRC cache was cleared.')
        ->assertSuccessful();

    expect(Cache::get('laravel-myanmar-nrc'))->toBeNull()
        ->and(MyanmarNrc::isValid('12-284-1-123456'))->toBeFalse();
});

it('clears the cache when seeding', function () {
    expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue();

    Township::query()->whereKey(284)->delete();
    $this->seedNrcTables();

    expect(Cache::get('laravel-myanmar-nrc'))->toBeNull()
        ->and(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue();
});

it('does not cache tables that are not seeded yet', function () {
    DB::table('nrc_townships')->delete();
    app(DatabaseNrcRepository::class)->flush();

    expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeFalse()
        ->and(Cache::get('laravel-myanmar-nrc'))->toBeNull();

    $this->seedNrcTables();

    expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue();
});
