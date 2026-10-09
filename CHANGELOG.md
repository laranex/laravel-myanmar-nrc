# Changelog

All notable changes to `laravel-myanmar-nrc` will be documented in this file

## v4.0.0 - Unreleased

There is no v3.0.0: every Laranex package moved to v4.0.0 together, so this release follows v2.0.0 directly.

### Changed
- Requires PHP 8.1+ and supports Laravel 10 through 13.
- Rebuilt on the official Laravel package skeleton (Pest, PHPStan, Pint, Testbench workbench, GitHub Actions matrix).
- The parser is `Laranex\LaravelMyanmarNRC\MyanmarNrc` with instance methods `parse(string $nrc, ?bool $dbDriven = null, ?string $lang = null): string` and `isValid(string $nrc, ?bool $dbDriven = null): bool`, replacing the static `LaravelMyanmarNrc::parseNRC()` and `isValidMyanmarNRC()`. It is bound as a singleton under its class name.
- The facade moved from `Laranex\LaravelMyanmarNRC\LaravelMyanmarNrcFacade` (alias `LaravelMyanmarNrc`) to `Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc` (alias `MyanmarNrc`); the `laravel-myanmar-nrc` container binding is gone.
- The service provider is `Laranex\LaravelMyanmarNRC\MyanmarNrcServiceProvider` (auto-discovered).
- Passing `$dbDriven = false` now really selects the JSON backend; before, `db_driven => true` in the config could not be overridden per call.
- Invalid NRCs throw `Laranex\LaravelMyanmarNRC\Exceptions\InvalidNrcException` and an unknown language throws `UnsupportedLocaleException` (both extend `InvalidArgumentException`) instead of a bare `Exception`. An NRC must have exactly four `-` separated segments.
- The seed command class is `Laranex\LaravelMyanmarNRC\Console\Commands\SeedMyanmarNrcCommand` (signature still `mm-nrc:seed`). It works on every database driver (no more MySQL-only `SET FOREIGN_KEY_CHECKS`), inserts in chunks and throws `InvalidJsonFileException` for a missing or malformed file.
- The JSON backend is `Laranex\LaravelMyanmarNRC\Repositories\JsonNrcRepository` and the database backend `DatabaseNrcRepository`, both implementing `NrcRepository`; `Data\MyanmarNRCJsonHandler` was removed. The bundled data moved to `resources/data/nrc.json`.
- `json_file` defaults to `null` (the bundled file). The old `'nrc.json'` value still means the bundled file.
- Model relationships are `State::townships()` and `Township::state()` (were `nrcTownships()` and `nrcTownship()`); `id`, `code` and `nrc_state_id` are cast to integers.
- The validation rule `Laranex\LaravelMyanmarNRC\Rules\MyanmarNRC` accepts an optional `dbDriven` constructor argument and lets the host application override its message through the usual custom-messages array.
- Publish tags: `laravel-myanmar-nrc` (everything), `laravel-myanmar-nrc-config`, `laravel-myanmar-nrc-lang` and `laravel-myanmar-nrc-migrations` (new). The migrations are published with a timestamp; once published, the package stops loading its own copies and publishing again reuses them.
- `isValid()` and the validation rule ignore the `locale` config, so an unsupported value there never makes validation throw `UnsupportedLocaleException`; only `parse()` reads it.
- `isValid()` only turns `InvalidNrcException` into `false`; database errors (e.g. a missing NRC table) now surface instead of being swallowed as in v2.

### Fixed
- NRC data: Myanmar codes and names use the letter `ဝ` instead of the Myanmar digit zero `၀` (e.g. `ပဝန`, `ဝမန`, `ကလဝ`, `ဧရာဝတီ`), and stray zero-width characters, Zawgyi-ordered text and a trailing letter were removed.
- NRC data: townships that carried another township's code now carry their own. Kachin `11` is `KAMANA`/`ကမန` (Kamee) and `16` (Kamaing) is `KAMATA`/`ကမတ`, `20` (Sinbo) is `SABANA`; Rakhine `267` (Maungdaw) is `MATANA`/`မတန` and `268` (Ponnagyun) is `PANAKA`/`ပဏက`; Shan `344` (Pangyang) is `PAYANA`/`ပယန` and `405` (Mongnawng) is `MANATA`/`မနတ`. The ids are unchanged, so stored NRCs keep validating; re-run `php artisan mm-nrc:seed` to update the tables.

### Upgrading
- Require `laranex/laravel-myanmar-nrc:^4.0` and make sure the application runs PHP 8.1+ on Laravel 10 or newer.
- Replace `LaravelMyanmarNrc::parseNRC($nrc, $dbDriven, $lang)` / `LaravelMyanmarNrcFacade::parseNRC(...)` with `MyanmarNrc::parse($nrc, $dbDriven, $lang)` and `(new LaravelMyanmarNrc)->isValidMyanmarNRC($nrc)` with `MyanmarNrc::isValid($nrc)`, importing `Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc`. Update any `LaravelMyanmarNrc` alias usage to `MyanmarNrc`.
- Catch `InvalidNrcException` (or `InvalidArgumentException`) where you caught `Exception` around `parseNRC()`.
- If you registered the provider manually, point it at `Laranex\LaravelMyanmarNRC\MyanmarNrcServiceProvider`.
- Rename `nrcTownships()` / `nrcTownship()` calls on the models to `townships()` / `state()`.
- If you referenced `Data\MyanmarNRCJsonHandler`, resolve `Repositories\JsonNrcRepository` from the container instead; its `types()`, `states()` and `townships()` return the rows as arrays.
- The migrations keep their file names, so existing installations do not need to migrate again. Re-run `php artisan mm-nrc:seed` once to refresh the data.

## 2.0.0

- Laravel 11 support

## 1.0.0 - 201X-XX-XX

- initial release

### 1.0.5

- NRC validator bug fixed
