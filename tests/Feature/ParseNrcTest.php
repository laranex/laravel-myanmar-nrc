<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Laranex\LaravelMyanmarNRC\Exceptions\InvalidNrcException;
use Laranex\LaravelMyanmarNRC\Exceptions\UnsupportedLocaleException;
use Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc;
use Laranex\LaravelMyanmarNRC\MyanmarNrc as Nrc;
use Laranex\LaravelMyanmarNRC\Repositories\DatabaseNrcRepository;
use Laranex\LaravelMyanmarNRC\Repositories\JsonNrcRepository;

beforeEach(function (): void {
    $this->seedNrcTables();
});

it('parses an NRC in English from the database', function () {
    expect(MyanmarNrc::parse('12-284-1-123456'))->toBe('12/DAGAYA(N)123456');
});

it('parses an NRC in Myanmar from the database', function () {
    expect(MyanmarNrc::parse('12-284-1-123456', lang: 'mm'))->toBe('၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆');
});

it('parses an NRC from the JSON file when asked to', function () {
    Schema::drop('nrc_states');

    expect(MyanmarNrc::parse('12-284-1-123456', dbDriven: false))->toBe('12/DAGAYA(N)123456')
        ->and(MyanmarNrc::parse('1-1-2-000001', false, 'mm'))->toBe('၁/ဟပန(ဧည့်)၀၀၀၀၀၁');
});

it('gives the same answer from both backends for every type', function (string $type, string $en, string $mm) {
    expect(MyanmarNrc::parse("13-331-{$type}-987654", true))->toBe("13/{$en}987654")
        ->and(MyanmarNrc::parse("13-331-{$type}-987654", false))->toBe("13/{$en}987654")
        ->and(MyanmarNrc::parse("13-331-{$type}-987654", true, 'mm'))->toBe("၁၃/{$mm}၉၈၇၆၅၄")
        ->and(MyanmarNrc::parse("13-331-{$type}-987654", false, 'mm'))->toBe("၁၃/{$mm}၉၈၇၆၅၄");
})->with([
    'naing' => ['1', 'PALANA(N)', 'ပလန(နိုင်)'],
    'ae' => ['2', 'PALANA(E)', 'ပလန(ဧည့်)'],
    'pyu' => ['3', 'PALANA(P)', 'ပလန(ပြု)'],
    'thathana' => ['4', 'PALANA(T)', 'ပလန(သာသနာ)'],
    'yayi' => ['5', 'PALANA(Y)', 'ပလန(ယာယီ)'],
    'sa' => ['6', 'PALANA(S)', 'ပလန(စ)'],
]);

it('formats townships with their own codes', function (string $nrc, string $en, string $mm) {
    expect(MyanmarNrc::parse($nrc))->toBe($en)
        ->and(MyanmarNrc::parse($nrc, false))->toBe($en)
        ->and(MyanmarNrc::parse($nrc, lang: 'mm'))->toBe($mm)
        ->and(MyanmarNrc::parse($nrc, false, 'mm'))->toBe($mm);
})->with([
    'the Myanmar letter wa, not the digit zero' => ['1-7-1-123456', '1/PAWANA(N)123456', '၁/ပဝန(နိုင်)၁၂၃၄၅၆'],
    'Kamaing' => ['1-16-1-123456', '1/KAMATA(N)123456', '၁/ကမတ(နိုင်)၁၂၃၄၅၆'],
    'Sinbo' => ['1-20-1-123456', '1/SABANA(N)123456', '၁/ဆဘန(နိုင်)၁၂၃၄၅၆'],
    'Maungdaw' => ['11-267-1-123456', '11/MATANA(N)123456', '၁၁/မတန(နိုင်)၁၂၃၄၅၆'],
    'Ponnagyun' => ['11-268-1-123456', '11/PANAKA(N)123456', '၁၁/ပဏက(နိုင်)၁၂၃၄၅၆'],
    'Pangyang' => ['13-344-1-123456', '13/PAYANA(N)123456', '၁၃/ပယန(နိုင်)၁၂၃၄၅၆'],
    'Mongnawng' => ['13-405-1-123456', '13/MANATA(N)123456', '၁၃/မနတ(နိုင်)၁၂၃၄၅၆'],
]);

it('reads the default language and backend from the config', function () {
    config()->set('laravel-myanmar-nrc.locale', 'mm');
    config()->set('laravel-myanmar-nrc.db_driven', false);
    $this->reloadNrc();

    expect(MyanmarNrc::parse('12-284-1-123456'))->toBe('၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆')
        ->and(MyanmarNrc::repository())->toBeInstanceOf(JsonNrcRepository::class)
        ->and(MyanmarNrc::repository(true))->toBeInstanceOf(DatabaseNrcRepository::class);
});

it('rejects malformed NRCs', function (string $nrc) {
    expect(fn () => MyanmarNrc::parse($nrc))->toThrow(InvalidNrcException::class, "Invalid NRC [{$nrc}].")
        ->and(MyanmarNrc::isValid($nrc))->toBeFalse()
        ->and(MyanmarNrc::isValid($nrc, false))->toBeFalse();
})->with([
    'empty' => [''],
    'human readable form' => ['12/DAGAYA(N)123456'],
    'too few segments' => ['12-284-123456'],
    'too many segments' => ['12-284-1-1-123456'],
    'five digit number' => ['12-284-1-12345'],
    'seven digit number' => ['12-284-1-1234567'],
    'letters in the number' => ['12-284-1-12345a'],
    'non numeric state' => ['x-284-1-123456'],
    'negative township' => ['12--284-1-123456'],
]);

it('rejects NRCs that point at unknown or mismatched data', function (string $nrc) {
    expect(fn () => MyanmarNrc::parse($nrc))->toThrow(InvalidNrcException::class)
        ->and(fn () => MyanmarNrc::parse($nrc, false))->toThrow(InvalidNrcException::class)
        ->and(MyanmarNrc::isValid($nrc))->toBeFalse()
        ->and(MyanmarNrc::isValid($nrc, false))->toBeFalse();
})->with([
    'unknown state' => ['16-284-1-123456'],
    'unknown township' => ['12-999-1-123456'],
    'unknown type' => ['12-284-7-123456'],
    'township from another state' => ['12-1-1-123456'],
    'state zero' => ['0-284-1-123456'],
]);

it('accepts the NRC types and townships it knows', function () {
    expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue()
        ->and(MyanmarNrc::isValid('12-284-1-123456', false))->toBeTrue()
        ->and(MyanmarNrc::isValid(' 15-471-6-000000 '))->toBeTrue();
});

it('only formats in English or Myanmar', function () {
    MyanmarNrc::parse('12-284-1-123456', lang: 'fr');
})->throws(UnsupportedLocaleException::class, 'Unsupported NRC locale [fr]. Only en and mm are allowed.');

it('lists its languages', function () {
    expect(Nrc::locales())->toBe(['en', 'mm']);
});

it('validates regardless of a misconfigured locale', function () {
    config()->set('laravel-myanmar-nrc.locale', 'fr');
    $this->reloadNrc();

    expect(MyanmarNrc::isValid('12-284-1-123456'))->toBeTrue()
        ->and(MyanmarNrc::isValid('12-1-1-123456'))->toBeFalse()
        ->and(MyanmarNrc::parse('12-284-1-123456', lang: 'en'))->toBe('12/DAGAYA(N)123456')
        ->and(fn () => MyanmarNrc::parse('12-284-1-123456'))->toThrow(UnsupportedLocaleException::class);
});
