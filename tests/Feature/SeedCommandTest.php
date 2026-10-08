<?php

declare(strict_types=1);

use Laranex\LaravelMyanmarNRC\Exceptions\InvalidJsonFileException;
use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;

it('seeds the NRC tables from the bundled JSON file', function () {
    $this->artisan('mm-nrc:seed')
        ->expectsOutputToContain('Seeded 6 types, 15 states and 471 townships.')
        ->assertSuccessful();

    expect(Type::query()->count())->toBe(6)
        ->and(State::query()->count())->toBe(15)
        ->and(Township::query()->count())->toBe(471)
        ->and(State::query()->find(12))->name->toBe('YANGON')->name_mm->toBe('ရန်ကုန်')->code->toBe(12)->code_mm->toBe('၁၂')
        ->and(Township::query()->find(284))->code->toBe('DAGAYA')->nrc_state_id->toBe(12)
        ->and(Type::query()->find(1))->code->toBe('N')->code_mm->toBe('နိုင်');
});

it('replaces the existing rows instead of duplicating them', function () {
    $this->artisan('mm-nrc:seed')->assertSuccessful();
    State::query()->create(['code' => 99, 'code_mm' => '၉၉', 'name' => 'STALE', 'name_mm' => 'STALE']);

    $this->artisan('mm-nrc:seed')->assertSuccessful();

    expect(State::query()->count())->toBe(15)
        ->and(State::query()->where('name', 'STALE')->exists())->toBeFalse()
        ->and(Township::query()->count())->toBe(471);
});

it('seeds from a custom JSON file', function () {
    $file = tempnam(sys_get_temp_dir(), 'nrc');
    file_put_contents((string) $file, json_encode([
        'types' => [['id' => 1, 'code' => 'N', 'code_mm' => 'နိုင်', 'name' => 'N', 'name_mm' => 'နိုင်']],
        'states' => [[
            'id' => 1, 'code' => 1, 'code_mm' => '၁', 'name' => 'TEST', 'name_mm' => 'စမ်း',
            'townships' => [['id' => 1, 'code' => 'TATA', 'code_mm' => 'တတ', 'name' => 'TEST', 'name_mm' => 'စမ်း']],
        ]],
    ]));
    config()->set('laravel-myanmar-nrc.json_file', $file);
    $this->reloadNrc();

    $this->artisan('mm-nrc:seed')
        ->expectsOutputToContain("Loading NRC data from [{$file}].")
        ->assertSuccessful();

    expect(Township::query()->count())->toBe(1)
        ->and(Township::query()->first())->code->toBe('TATA')->nrc_state_id->toBe(1)
        ->and(Township::query()->first()?->state)->toBeInstanceOf(State::class)->name->toBe('TEST')
        ->and(State::query()->first()?->townships)->toHaveCount(1);

    unlink((string) $file);
});

it('fails loudly when the JSON file is missing', function () {
    config()->set('laravel-myanmar-nrc.json_file', '/nowhere/nrc.json');
    $this->reloadNrc();

    $this->artisan('mm-nrc:seed');
})->throws(InvalidJsonFileException::class, 'The NRC JSON file [/nowhere/nrc.json] could not be read.');

it('fails loudly when the JSON file has the wrong shape', function () {
    $file = tempnam(sys_get_temp_dir(), 'nrc');
    file_put_contents((string) $file, '{"states": []}');
    config()->set('laravel-myanmar-nrc.json_file', $file);
    $this->reloadNrc();

    try {
        $this->artisan('mm-nrc:seed');
    } finally {
        unlink((string) $file);
    }
})->throws(InvalidJsonFileException::class, 'must contain "types" and "states" arrays');
