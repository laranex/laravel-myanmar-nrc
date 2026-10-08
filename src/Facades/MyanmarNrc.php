<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Facades;

use Illuminate\Support\Facades\Facade;
use Laranex\LaravelMyanmarNRC\Repositories\NrcRepository;

/**
 * @method static string parse(string $nrc, ?bool $dbDriven = null, ?string $lang = null)
 * @method static bool isValid(string $nrc, ?bool $dbDriven = null)
 * @method static NrcRepository repository(?bool $dbDriven = null)
 *
 * @see \Laranex\LaravelMyanmarNRC\MyanmarNrc
 */
class MyanmarNrc extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Laranex\LaravelMyanmarNRC\MyanmarNrc::class;
    }
}
