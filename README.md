# LARAVEL MYANMAR NRC

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laranex/laravel-myanmar-nrc.svg?style=flat-square)](https://packagist.org/packages/laranex/laravel-myanmar-nrc)
[![Total Downloads](https://img.shields.io/packagist/dt/laranex/laravel-myanmar-nrc.svg?style=flat-square)](https://packagist.org/packages/laranex/laravel-myanmar-nrc)

A Laravel package to deal with NRC from Myanmar.

## Installation

You can install the package via composer:

```bash
composer require laranex/laravel-myanmar-nrc

#This will create required tables for handing NRC from Myanmar
php artisan migrate

#This will read the nrc data from predefined json file and insert into database
php artisan mm-nrc:seed
```

## Configuration

```bash
php artisan vendor:publish --tag="laravel-myanmar-nrc"
```


| Option      | Description                                                                                                                            | Default Value |
|-------------|----------------------------------------------------------------------------------------------------------------------------------------|--------------|
| locale      | Locale to be use to parse the NRC                                                                                                      | en           |
| json_file   | Path of the predefined json file, you can replace with yours and do not forget to re-run ```php php artisan mm-nrc:seed``` after that. | nrc.json     |
| db_driven   | Defines if the application use database or json file to validate and parse the nrc                                                     | true         |


## Supported Functions

 - Validation of NRC
 - Full set of predefined [NRC List](src/Data/nrc.json) (You can replace or override with yours) 
 - Multiple language for NRC (Myanmar, English) 

## NOTE

- This package use ID based network communication to secure the NRC data & enhance the Validation, Parsing.

- example format of NRC data from a network request
    - X-X-X-12345
    - Where the very fist X represents the ID of the STATE in database/json, the second X is for TOWNSHIP and the last is for TYPE of the NRC.


## Usage

```php

    //Validation
    new \laranex\LaravelMyanmarNRC\Rules\MyanmarNRC()
    
    //Parsing the NRC from network
    \laranex\LaravelMyanmarNRC\LaravelMyanmarNrcFacade::parseNRC($nrc, $dbDriven, $lang)
        (OR)
    \laranex\LaravelMyanmarNRC\LaravelMyanmarNrc::parseNRC($nrc, $dbDriven, $lang)
    
    
    //$nrc represents the nrc (X-X-X-12345) from network,
    //$drDriven defines if the application use database or predefined json file to parse, validate the nrc and,
    //$lang is for the Languages (mm and en are supported) to be used in parsing.

    //default values for $dbDriven & $lang will be read from config file if you do not pass the values to the method.
```



### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

### Security

If you discover any security related issues, please email naythukhant644@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
