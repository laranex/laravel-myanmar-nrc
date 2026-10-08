<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Repositories;

use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;

interface NrcRepository
{
    /**
     * Find a state or region by its id.
     */
    public function state(int $id): ?State;

    /**
     * Find a township by its id.
     */
    public function township(int $id): ?Township;

    /**
     * Find an NRC type (citizenship class) by its id.
     */
    public function type(int $id): ?Type;
}
