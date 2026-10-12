<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Repositories;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Model;
use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;

/**
 * Reads the nrc_states, nrc_townships and nrc_types tables.
 *
 * The three tables are loaded once and kept in memory for the life of the
 * process, and in the given Laravel cache store (if any) so other workers
 * skip the queries too. Call flush() (or "php artisan mm-nrc:clear-cache")
 * after changing the tables; "mm-nrc:seed" does it for you.
 *
 * @phpstan-type Row array<string, mixed>
 * @phpstan-type Rows array{states: array<int, Row>, townships: array<int, Row>, types: array<int, Row>}
 */
class DatabaseNrcRepository implements NrcRepository
{
    /** @var Rows|null */
    private ?array $rows = null;

    /**
     * @param  Cache|null  $cache  The store shared between processes; null keeps the data in memory only.
     * @param  int|null  $ttl  Seconds to keep the data in the store; null keeps it forever.
     */
    public function __construct(
        private readonly ?Cache $cache = null,
        private readonly string $key = 'laravel-myanmar-nrc',
        private readonly ?int $ttl = null,
    ) {}

    public function state(int $id): ?State
    {
        $row = $this->rows()['states'][$id] ?? null;

        return $row === null ? null : $this->hydrate(new State, $row);
    }

    public function township(int $id): ?Township
    {
        $row = $this->rows()['townships'][$id] ?? null;

        return $row === null ? null : $this->hydrate(new Township, $row);
    }

    public function type(int $id): ?Type
    {
        $row = $this->rows()['types'][$id] ?? null;

        return $row === null ? null : $this->hydrate(new Type, $row);
    }

    /**
     * Forget the loaded data, in memory and in the cache store, so the next
     * lookup reads the tables again.
     */
    public function flush(): void
    {
        $this->rows = null;
        $this->cache?->forget($this->key);
    }

    /**
     * @return Rows
     */
    private function rows(): array
    {
        if ($this->rows !== null) {
            return $this->rows;
        }

        $cached = $this->cache?->get($this->key);

        if (is_array($cached) && $this->isRows($cached)) {
            /** @var Rows $cached */
            return $this->rows = $cached;
        }

        $rows = [
            'states' => $this->load(new State),
            'townships' => $this->load(new Township),
            'types' => $this->load(new Type),
        ];

        // Tables that are not seeded yet are read again on the next lookup.
        if ($rows['states'] === [] || $rows['townships'] === [] || $rows['types'] === []) {
            return $rows;
        }

        if ($this->ttl === null) {
            $this->cache?->forever($this->key, $rows);
        } else {
            $this->cache?->put($this->key, $rows, $this->ttl);
        }

        return $this->rows = $rows;
    }

    /**
     * @return array<int, Row>
     */
    private function load(Model $model): array
    {
        $rows = [];

        foreach ($model->newQuery()->toBase()->get() as $row) {
            $row = (array) $row;
            $id = $row['id'] ?? null;

            if (is_int($id) || is_string($id)) {
                $rows[(int) $id] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  array<mixed>  $value
     */
    private function isRows(array $value): bool
    {
        return is_array($value['states'] ?? null) && is_array($value['townships'] ?? null) && is_array($value['types'] ?? null);
    }

    /**
     * @template TModel of Model
     *
     * @param  TModel  $model
     * @param  Row  $row
     * @return TModel
     */
    private function hydrate(Model $model, array $row): Model
    {
        return $model->newFromBuilder($row);
    }
}
