# Laravel Myanmar NRC

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laranex/laravel-myanmar-nrc.svg?style=flat-square)](https://packagist.org/packages/laranex/laravel-myanmar-nrc)
[![Tests](https://img.shields.io/github/actions/workflow/status/laranex/laravel-myanmar-nrc/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/laranex/laravel-myanmar-nrc/actions/workflows/tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/laranex/laravel-myanmar-nrc.svg?style=flat-square)](https://packagist.org/packages/laranex/laravel-myanmar-nrc)
[![License](https://img.shields.io/packagist/l/laranex/laravel-myanmar-nrc.svg?style=flat-square)](LICENSE.md)

Validate and parse Myanmar National Registration Card (NRC) numbers in a Laravel application. Clients send an id based NRC (`stateId-townshipId-typeId-123456`, so the state, township and type come from a list you control), and the package checks that the ids exist and belong together, then formats the number in English (`12/DAGAYA(N)123456`) or Myanmar (`၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆`). It ships every state, township and NRC type as a JSON file, which can be read directly or seeded into your database.

## Documentation

Full documentation lives at **[laranex.vercel.app/laravel-myanmar-nrc](https://laranex.vercel.app/laravel-myanmar-nrc)**.

## Requirements

- PHP 8.1 or higher
- Laravel 10, 11, 12 or 13

## Installation

```bash
composer require laranex/laravel-myanmar-nrc
```

The package registers its migrations automatically. To use the database backend (the default), create and fill the `nrc_states`, `nrc_townships` and `nrc_types` tables:

```bash
php artisan migrate
php artisan mm-nrc:seed
```

You may publish the configuration file and the translations:

```bash
php artisan vendor:publish --tag="laravel-myanmar-nrc-config"
php artisan vendor:publish --tag="laravel-myanmar-nrc-lang"
```

The config holds three keys: `locale` (`en` or `mm`, the default output language), `json_file` (`null` for the bundled data file, or the path of your own copy) and `db_driven` (`true` to read from the database, `false` to read the JSON file directly, with no database needed).

## Usage

```php
use Laranex\LaravelMyanmarNRC\Facades\MyanmarNrc;
use Laranex\LaravelMyanmarNRC\Rules\MyanmarNRC;

// Validate the id based NRC a client sent
$validated = $request->validate([
    'nrc' => ['required', new MyanmarNRC],
]);

// Format it for humans, in English or Myanmar
MyanmarNrc::parse($validated['nrc']);              // "12/DAGAYA(N)123456"
MyanmarNrc::parse($validated['nrc'], lang: 'mm');  // "၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆"

// Check without throwing, or read the JSON file instead of the database
MyanmarNrc::isValid('12-1-1-123456');              // false: township 1 is not in state 12
MyanmarNrc::parse('12-284-1-123456', dbDriven: false);
```

`parse()` throws `Laranex\LaravelMyanmarNRC\Exceptions\InvalidNrcException` for an unknown or mismatched NRC. The validation message is translated through the `laravel-myanmar-nrc::validation.invalid` key (English and Myanmar are bundled). Build the pick lists for your forms from the `State`, `Township` and `Type` models or from the bundled [NRC data](resources/data/nrc.json).

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Nay Thu Khant](https://github.com/NayThuKhant)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
