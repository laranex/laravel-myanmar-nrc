<?php

declare(strict_types=1);

use Laranex\LaravelMyanmarNRC\Exceptions\InvalidJsonFileException;
use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;
use Laranex\LaravelMyanmarNRC\Repositories\JsonNrcRepository;

it('reads the bundled file by default', function () {
    $repository = new JsonNrcRepository;

    expect($repository->path())->toBe(JsonNrcRepository::bundledPath())
        ->and(file_exists($repository->path()))->toBeTrue()
        ->and($repository->types())->toHaveCount(6)
        ->and($repository->states())->toHaveCount(15)
        ->and($repository->townships())->toHaveCount(471);
});

it('hydrates models from the JSON rows', function () {
    $repository = new JsonNrcRepository;

    expect($repository->state(12))->toBeInstanceOf(State::class)->id->toBe(12)->code->toBe(12)->name->toBe('YANGON')
        ->and($repository->township(284))->toBeInstanceOf(Township::class)->code->toBe('DAGAYA')->nrc_state_id->toBe(12)
        ->and($repository->type(1))->toBeInstanceOf(Type::class)->code->toBe('N')->code_mm->toBe('နိုင်')
        ->and($repository->state(99))->toBeNull()
        ->and($repository->township(0))->toBeNull()
        ->and($repository->type(7))->toBeNull();
});

it('ships consistent data', function () {
    $repository = new JsonNrcRepository;
    $stateIds = array_column($repository->states(), 'id');
    $townshipIds = array_column($repository->townships(), 'id');

    expect($stateIds)->toBe(range(1, 15))
        ->and(array_unique($townshipIds))->toHaveCount(471)
        ->and(array_unique(array_column($repository->townships(), 'nrc_state_id')))->toHaveCount(15);

    foreach ($repository->townships() as $township) {
        expect($township)->toHaveKeys(['id', 'nrc_state_id', 'code', 'code_mm', 'name', 'name_mm'])
            ->and($township['nrc_state_id'])->toBeIn($stateIds);
    }
});

it('maps the legacy and empty config values to the bundled file', function () {
    expect(JsonNrcRepository::resolvePath(null))->toBe(JsonNrcRepository::bundledPath())
        ->and(JsonNrcRepository::resolvePath(''))->toBe(JsonNrcRepository::bundledPath())
        ->and(JsonNrcRepository::resolvePath('nrc.json'))->toBe(JsonNrcRepository::bundledPath())
        ->and(JsonNrcRepository::resolvePath(false))->toBe(JsonNrcRepository::bundledPath())
        ->and(JsonNrcRepository::resolvePath('/srv/nrc.json'))->toBe('/srv/nrc.json');
});

it('rejects an unreadable file', function () {
    (new JsonNrcRepository('/nowhere/nrc.json'))->states();
})->throws(InvalidJsonFileException::class, 'could not be read');

it('rejects a file without types and states', function (string $json) {
    $file = (string) tempnam(sys_get_temp_dir(), 'nrc');
    file_put_contents($file, $json);

    try {
        (new JsonNrcRepository($file))->types();
    } finally {
        unlink($file);
    }
})->with([
    'not json' => ['not json'],
    'a list' => ['[1, 2, 3]'],
    'missing types' => ['{"states": []}'],
    'missing states' => ['{"types": []}'],
])->throws(InvalidJsonFileException::class, 'must contain "types" and "states" arrays');

it('ignores unknown keys and keeps only scalar values', function () {
    $file = (string) tempnam(sys_get_temp_dir(), 'nrc');
    file_put_contents($file, json_encode([
        'types' => [['id' => 1, 'code' => 'N', 'code_mm' => 'နိုင်', 'name' => 'N', 'name_mm' => 'နိုင်', 'extra' => [1]]],
        'states' => [['id' => 1, 'code' => 1, 'code_mm' => '၁', 'name' => 'A', 'name_mm' => 'က', 'townships' => 'oops']],
    ]));

    try {
        $repository = new JsonNrcRepository($file);

        expect($repository->types())->toBe([['id' => 1, 'code' => 'N', 'code_mm' => 'နိုင်', 'name' => 'N', 'name_mm' => 'နိုင်']])
            ->and($repository->states())->toBe([['id' => 1, 'code' => 1, 'code_mm' => '၁', 'name' => 'A', 'name_mm' => 'က']])
            ->and($repository->townships())->toBe([]);
    } finally {
        unlink($file);
    }
});
