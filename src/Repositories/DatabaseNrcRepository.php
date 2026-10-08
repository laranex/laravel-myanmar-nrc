<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Repositories;

use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;

class DatabaseNrcRepository implements NrcRepository
{
    public function state(int $id): ?State
    {
        return State::query()->find($id);
    }

    public function township(int $id): ?Township
    {
        return Township::query()->find($id);
    }

    public function type(int $id): ?Type
    {
        return Type::query()->find($id);
    }
}
