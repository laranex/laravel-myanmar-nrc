<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Exceptions;

use InvalidArgumentException;

class InvalidNrcException extends InvalidArgumentException
{
    public static function for(string $nrc): self
    {
        return new self(sprintf('Invalid NRC [%s].', $nrc));
    }
}
