<?php

namespace laranex\LaravelMyanmarNRC\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use laranex\LaravelMyanmarNRC\LaravelMyanmarNrc;

class MyanmarNRC implements InvokableRule
{
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        if (! (new LaravelMyanmarNrc)->isValidMyanmarNRC($value)) {
            $fail('laravel-myanmar-nrc::validation.invalid')->translate();
        }
    }
}
