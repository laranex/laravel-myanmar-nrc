<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Laranex\LaravelMyanmarNRC\Rules\MyanmarNRC;

beforeEach(function (): void {
    $this->seedNrcTables();
});

it('passes a valid NRC', function () {
    $validator = Validator::make(['nrc' => '12-284-1-123456'], ['nrc' => ['required', new MyanmarNRC]]);

    expect($validator->passes())->toBeTrue();
});

it('fails an invalid NRC with the English message', function () {
    $validator = Validator::make(['nrc' => '12-1-1-123456'], ['nrc' => ['required', new MyanmarNRC]]);

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('nrc'))->toBe('The nrc is not valid.');
});

it('translates the message when the app runs in Myanmar', function () {
    app()->setLocale('mm');

    $validator = Validator::make(['nrc' => 'not-an-nrc'], ['nrc' => new MyanmarNRC]);

    expect($validator->errors()->first('nrc'))->toBe('မှတ်ပုံတင်သည် အကျုံးမဝင်ပါ။');
});

it('rejects values that are not strings', function (mixed $value) {
    $validator = Validator::make(['nrc' => $value], ['nrc' => new MyanmarNRC]);

    expect($validator->fails())->toBeTrue();
})->with([
    'array' => [['12-284-1-123456']],
    'boolean' => [true],
    'null' => [null],
]);

it('can validate against the JSON file instead of the database', function () {
    Schema::drop('nrc_townships');

    expect(Validator::make(['nrc' => '12-284-1-123456'], ['nrc' => new MyanmarNRC(dbDriven: false)])->passes())->toBeTrue()
        ->and(Validator::make(['nrc' => '12-1-1-123456'], ['nrc' => new MyanmarNRC(dbDriven: false)])->passes())->toBeFalse();
});

it('follows the db_driven config when no backend is given', function () {
    config()->set('laravel-myanmar-nrc.db_driven', false);
    $this->reloadNrc();
    Schema::drop('nrc_townships');

    expect(Validator::make(['nrc' => '12-284-1-123456'], ['nrc' => new MyanmarNRC])->passes())->toBeTrue();
});

it('lets the host application override the message', function () {
    $validator = Validator::make(['nrc' => 'nope'], ['nrc' => new MyanmarNRC], ['nrc.'.MyanmarNRC::class => 'Custom message']);

    expect($validator->errors()->first('nrc'))->toBe('Custom message');
});
