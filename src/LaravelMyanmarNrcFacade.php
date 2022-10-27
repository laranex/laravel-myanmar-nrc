<?php

namespace Laranex\LaravelMyanmarNRC;

use Illuminate\Support\Facades\Facade;

class LaravelMyanmarNrcFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-myanmar-nrc';
    }
}
