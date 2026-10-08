---
name: laravel-myanmar-nrc-development
description: >
  Validate and parse Myanmar NRC numbers in a Laravel app with laranex/laravel-myanmar-nrc, in English or Myanmar, from the database or the bundled JSON file.
license: MIT
metadata:
  author: Nay Thu Khant
---

# Laravel Myanmar NRC

Use this skill when a Laravel application collects, validates or displays Myanmar National Registration Card (NRC) numbers.

## Primary Goal

- accept NRCs as `stateId-townshipId-typeId-123456` (ids from the package's data), validate them with the package rule and format them with the facade instead of hand-written regexes or lookup tables

## Workflow

### 1. Choose the backend

- database (default, `db_driven => true`): run `php artisan migrate` (the package registers its migrations) and `php artisan mm-nrc:seed` to fill `nrc_states`, `nrc_townships` and `nrc_types`; re-run the seed command after changing the data file
- to customise the tables, publish the migrations before the first `migrate`: `php artisan vendor:publish --tag="laravel-myanmar-nrc-migrations"` (the package then skips its own copies); never publish them in an app that already ran the package migrations
- JSON (`db_driven => false`): nothing to migrate or seed, the file is read on first use
- publish the config only when a key must change: `php artisan vendor:publish --tag="laravel-myanmar-nrc-config"`; keys are `locale` (`en`|`mm`), `json_file` (`null` for the bundled file or a path) and `db_driven`

### 2. Validate input

- add `new \Laranex\LaravelMyanmarNRC\Rules\MyanmarNRC` to the request rules; `new MyanmarNRC(dbDriven: false)` forces the JSON file for that rule
- the message comes from `laravel-myanmar-nrc::validation.invalid` (English and Myanmar bundled); publish with `--tag="laravel-myanmar-nrc-lang"` to change it, or pass a custom message keyed by the rule class

### 3. Format for display

- `MyanmarNrc::parse($nrc)` (facade `Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc`) returns `12/DAGAYA(N)123456`; `MyanmarNrc::parse($nrc, lang: 'mm')` returns `၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆`
- `MyanmarNrc::isValid($nrc)` returns a bool and ignores the `locale` config (database errors still throw); `parse()` throws `InvalidNrcException` (an `InvalidArgumentException`) for unknown or mismatched ids and `UnsupportedLocaleException` for a language other than `en`/`mm`
- build form pick lists from the `State`, `Township` (with `state()` / `townships()` relations) and `Type` models, or from `app(JsonNrcRepository::class)->states()` when there is no database

## Rules, References, and Templates

- no additional resource files for this skill

## Examples

- Request validation: `$request->validate(['nrc' => ['required', new MyanmarNRC]]);`
- Store the id form and show the human form: `$user->nrc = $validated['nrc']; echo MyanmarNrc::parse($user->nrc, lang: app()->getLocale() === 'mm' ? 'mm' : 'en');`
- Feature test in the consuming app: `$this->artisan('mm-nrc:seed'); $this->postJson('/profile', ['nrc' => '12-284-1-123456'])->assertValid('nrc');`

## Anti-patterns

- do not store or validate the human readable form (`12/DAGAYA(N)123456`); the package only parses the id based form
- do not call `parse()` in a loop against the database backend when the JSON backend would do; `dbDriven: false` needs no queries
- do not seed with your own inserts; `mm-nrc:seed` truncates and reloads all three tables so ids stay in sync with the data file
