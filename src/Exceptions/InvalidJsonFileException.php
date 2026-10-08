<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Exceptions;

use RuntimeException;

class InvalidJsonFileException extends RuntimeException
{
    public static function unreadable(string $path): self
    {
        return new self(sprintf('The NRC JSON file [%s] could not be read.', $path));
    }

    public static function malformed(string $path): self
    {
        return new self(sprintf('The NRC JSON file [%s] must contain "types" and "states" arrays.', $path));
    }
}
