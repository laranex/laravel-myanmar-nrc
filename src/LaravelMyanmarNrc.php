<?php

namespace Laranex\LaravelMyanmarNRC;

use Exception;

class LaravelMyanmarNrc extends LaravelMyanmarNrcParser
{
    public function isValidMyanmarNRC($nrc): bool
    {
        try {
            $this->parseNRC($nrc);

            return true;
        } catch (Exception $_) {
            return false;
        }
    }
}
