<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Repositories;

use Laranex\LaravelMyanmarNRC\Exceptions\InvalidJsonFileException;
use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;

/**
 * @phpstan-type Row array<string, int|string>
 */
class JsonNrcRepository implements NrcRepository
{
    /** @var list<Row>|null */
    private ?array $types = null;

    /** @var list<Row>|null */
    private ?array $states = null;

    /** @var list<Row>|null */
    private ?array $townships = null;

    private readonly string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? self::bundledPath();
    }

    /**
     * The data file bundled with the package.
     */
    public static function bundledPath(): string
    {
        return dirname(__DIR__, 2).'/resources/data/nrc.json';
    }

    /**
     * Resolve the file to read from the configured "json_file" value.
     *
     * Null (and the legacy "nrc.json" value) means the bundled file.
     */
    public static function resolvePath(mixed $configured): string
    {
        if (! is_string($configured) || $configured === '' || $configured === 'nrc.json') {
            return self::bundledPath();
        }

        return $configured;
    }

    /**
     * The path of the JSON file being read.
     */
    public function path(): string
    {
        return $this->path;
    }

    public function state(int $id): ?State
    {
        $row = $this->find($this->states(), $id);

        return $row === null ? null : (new State)->forceFill($row);
    }

    public function township(int $id): ?Township
    {
        $row = $this->find($this->townships(), $id);

        return $row === null ? null : (new Township)->forceFill($row);
    }

    public function type(int $id): ?Type
    {
        $row = $this->find($this->types(), $id);

        return $row === null ? null : (new Type)->forceFill($row);
    }

    /**
     * Every NRC type as a database row.
     *
     * @return list<Row>
     */
    public function types(): array
    {
        $this->load();

        return $this->types ?? [];
    }

    /**
     * Every state or region as a database row (without its townships).
     *
     * @return list<Row>
     */
    public function states(): array
    {
        $this->load();

        return $this->states ?? [];
    }

    /**
     * Every township as a database row, including its "nrc_state_id".
     *
     * @return list<Row>
     */
    public function townships(): array
    {
        $this->load();

        return $this->townships ?? [];
    }

    /**
     * @param  list<Row>  $rows
     * @return Row|null
     */
    private function find(array $rows, int $id): ?array
    {
        foreach ($rows as $row) {
            if ((int) ($row['id'] ?? 0) === $id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * @throws InvalidJsonFileException
     */
    private function load(): void
    {
        if ($this->types !== null) {
            return;
        }

        $json = is_file($this->path) ? file_get_contents($this->path) : false;

        if ($json === false) {
            throw InvalidJsonFileException::unreadable($this->path);
        }

        $data = json_decode($json, true);

        if (! is_array($data) || ! is_array($data['types'] ?? null) || ! is_array($data['states'] ?? null)) {
            throw InvalidJsonFileException::malformed($this->path);
        }

        $types = [];
        $states = [];
        $townships = [];

        foreach ($data['types'] as $type) {
            $types[] = $this->row($type, ['id', 'code', 'code_my', 'name', 'name_my']);
        }

        foreach ($data['states'] as $state) {
            $states[] = $this->row($state, ['id', 'code', 'code_my', 'name', 'name_my']);

            $stateTownships = is_array($state) && is_array($state['townships'] ?? null) ? $state['townships'] : [];

            foreach ($stateTownships as $township) {
                $row = $this->row($township, ['id', 'code', 'code_my', 'name', 'name_my']);
                $row['nrc_state_id'] = (int) ($states[count($states) - 1]['id'] ?? 0);

                $townships[] = $row;
            }
        }

        $this->types = $types;
        $this->states = $states;
        $this->townships = $townships;
    }

    /**
     * @param  list<string>  $keys
     * @return Row
     */
    private function row(mixed $raw, array $keys): array
    {
        $row = [];

        foreach ($keys as $key) {
            $value = is_array($raw) ? ($raw[$key] ?? null) : null;

            if (is_int($value) || is_string($value)) {
                $row[$key] = $value;
            }
        }

        return $row;
    }
}
