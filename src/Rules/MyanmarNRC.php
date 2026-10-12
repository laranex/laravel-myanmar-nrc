<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Rules;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Validation\ValidationRule;
use Laranex\LaravelMyanmarNRC\MyanmarNrc as Nrc;

/**
 * Validates an id based NRC ("stateId-townshipId-typeId-123456").
 *
 * Zero-padded ids and surrounding whitespace pass, so store the value from
 * MyanmarNrc::normalize() ("12-284-1-123456") rather than the raw input.
 */
class MyanmarNRC implements ValidationRule
{
    /**
     * @param  bool|null  $dbDriven  Use the database (true) or the JSON file (false); defaults to the "db_driven" config value.
     */
    public function __construct(private readonly ?bool $dbDriven = null) {}

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valid = (is_string($value) || is_int($value))
            && Container::getInstance()->make(Nrc::class)->isValid((string) $value, $this->dbDriven);

        if (! $valid) {
            $fail('laravel-myanmar-nrc::validation.invalid')->translate();
        }
    }
}
