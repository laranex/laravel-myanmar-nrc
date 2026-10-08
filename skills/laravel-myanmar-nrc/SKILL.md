---
name: laravel-myanmar-nrc
description: >
  Validate Myanmar NRC numbers and format them in English or Myanmar in a Laravel app with laranex/laravel-myanmar-nrc, reading the bundled state, township and type data from the database or the JSON file.
license: MIT
metadata:
  author: Nay Thu Khant
---

# Laravel Myanmar NRC

## When to use

- A Laravel app collects, validates or displays Myanmar National Registration Card (NRC) numbers.
- Clients send the id based form `stateId-townshipId-typeId-123456` (ids from the package data, e.g. `12-284-1-123456`); the package checks that the state, township and type exist and that the township belongs to the state, then formats it as `12/DAGAYA(N)123456` or `၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆`.
- Use it instead of hand-written regexes or lookup tables.

## Install

```bash
composer require laranex/laravel-myanmar-nrc
```

Database backend (the default): the package registers its migrations, then the seed command fills `nrc_states`, `nrc_townships` and `nrc_types`.

```bash
php artisan migrate
php artisan mm-nrc:seed
```

To customize the tables, publish the migrations before the first `migrate`; the package then stops loading its own copies. Never publish them in an app that already ran the package migrations.

```bash
php artisan vendor:publish --tag="laravel-myanmar-nrc-migrations"
```

JSON backend: set `db_driven` to `false`; nothing to migrate or seed.

## Configure

Publish only when a key must change:

```bash
php artisan vendor:publish --tag="laravel-myanmar-nrc-config"
php artisan vendor:publish --tag="laravel-myanmar-nrc-lang"
```

`config/laravel-myanmar-nrc.php`:

- `locale`: `en` (default) or `mm`, the default output language of `parse()`; validation ignores it.
- `json_file`: `null` for the bundled data file, or the path of your own copy (e.g. `storage_path('nrc.json')`); it feeds `mm-nrc:seed` and the JSON backend.
- `db_driven`: `true` (default) reads the database tables, `false` reads the JSON file.

## Use

### Validate input

```php
use Laranex\LaravelMyanmarNRC\Rules\MyanmarNRC;

$validated = $request->validate([
    'nrc' => ['required', new MyanmarNRC],
]);

// Force the JSON file for this rule only
$request->validate(['nrc' => ['required', new MyanmarNRC(dbDriven: false)]]);
```

The message uses the `laravel-myanmar-nrc::validation.invalid` translation key (English and Myanmar bundled). Publish the `laravel-myanmar-nrc-lang` tag to change it.

### Format for display

```php
use Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc;

MyanmarNrc::parse('12-284-1-123456');                  // "12/DAGAYA(N)123456"
MyanmarNrc::parse('12-284-1-123456', lang: 'mm');      // "၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆"
MyanmarNrc::parse('12-284-1-123456', dbDriven: false); // read the JSON file
MyanmarNrc::isValid('12-1-1-123456');                  // false: township 1 is not in state 12
```

- `parse()` throws `Laranex\LaravelMyanmarNRC\Exceptions\InvalidNrcException` (an `InvalidArgumentException`) for a malformed, unknown or mismatched NRC, and `UnsupportedLocaleException` for a language other than `en` or `mm`.
- `isValid()` returns a bool and never throws for bad input (database errors still throw).

### Build form pick lists

- Database: the Eloquent models `Laranex\LaravelMyanmarNRC\Models\State` (`townships()` relation), `Township` (`state()` relation, `nrc_state_id`) and `Type`, each with `code`, `code_mm`, `name` and `name_mm`.
- JSON: `app(\Laranex\LaravelMyanmarNRC\Repositories\JsonNrcRepository::class)->states()`, `->townships()` and `->types()`.

## Test your app

```php
it('accepts a valid NRC', function () {
    $this->artisan('mm-nrc:seed');

    $this->postJson('/profile', ['nrc' => '12-284-1-123456'])->assertValid('nrc');
    $this->postJson('/profile', ['nrc' => '12-1-1-123456'])->assertInvalid('nrc');
});
```

With `RefreshDatabase`, seed in each test (or set `laravel-myanmar-nrc.db_driven` to `false` in the test to skip the database).

## Avoid

- Storing or validating the human readable form (`12/DAGAYA(N)123456`); the package validates and parses only the id based form. Store the id form and format on display.
- Filling the tables with your own inserts; `mm-nrc:seed` truncates and reloads all three tables so the ids match the data file. Re-run it after changing `json_file`.
- Publishing the migrations after they already ran.
