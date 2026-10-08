<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Exceptions;

use InvalidArgumentException;
use Laranex\LaravelMyanmarNRC\MyanmarNrc;

class UnsupportedLocaleException extends InvalidArgumentException
{
    public static function for(string $locale): self
    {
        return new self(sprintf('Unsupported NRC locale [%s]. Only %s are allowed.', $locale, implode(' and ', MyanmarNrc::locales())));
    }
}
