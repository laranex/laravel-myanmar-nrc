<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC;

use Laranex\LaravelMyanmarNRC\Exceptions\InvalidNrcException;
use Laranex\LaravelMyanmarNRC\Exceptions\UnsupportedLocaleException;
use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;
use Laranex\LaravelMyanmarNRC\Repositories\NrcRepository;

class MyanmarNrc
{
    public function __construct(
        private readonly NrcRepository $database,
        private readonly NrcRepository $json,
        private readonly string $locale = 'en',
        private readonly bool $dbDriven = true,
    ) {}

    /**
     * The languages a parsed NRC can be formatted in.
     *
     * @return list<string>
     */
    public static function locales(): array
    {
        return ['en', 'mm'];
    }

    /**
     * Parse an id based NRC ("stateId-townshipId-typeId-123456") into its
     * human readable form, e.g. "12/DAGAYA(N)123456" or "၁၂/ဒဂရ(နိုင်)၁၂၃၄၅၆".
     *
     * @param  bool|null  $dbDriven  Use the database (true) or the JSON file (false); defaults to the "db_driven" config value.
     * @param  string|null  $lang  "en" or "mm"; defaults to the "locale" config value.
     *
     * @throws InvalidNrcException
     * @throws UnsupportedLocaleException
     */
    public function parse(string $nrc, ?bool $dbDriven = null, ?string $lang = null): string
    {
        $lang ??= $this->locale;

        if (! in_array($lang, self::locales(), true)) {
            throw UnsupportedLocaleException::for($lang);
        }

        [$state, $township, $type, $number] = $this->resolve($nrc, $dbDriven);

        if ($lang === 'mm') {
            return sprintf('%s/%s(%s)%s', $state->code_mm, $township->code_mm, $type->code_mm, $this->toMyanmarDigits($number));
        }

        return sprintf('%d/%s(%s)%s', $state->code, $township->code, $type->code, $number);
    }

    /**
     * Determine whether an id based NRC refers to a real state, township and type.
     *
     * @param  bool|null  $dbDriven  Use the database (true) or the JSON file (false); defaults to the "db_driven" config value.
     */
    public function isValid(string $nrc, ?bool $dbDriven = null): bool
    {
        try {
            $this->resolve($nrc, $dbDriven);

            return true;
        } catch (InvalidNrcException) {
            return false;
        }
    }

    /**
     * Look up the state, township and type an id based NRC refers to.
     *
     * Independent of the output language, so a misconfigured "locale" never
     * affects validation.
     *
     * @return array{0: State, 1: Township, 2: Type, 3: string}
     *
     * @throws InvalidNrcException
     */
    private function resolve(string $nrc, ?bool $dbDriven): array
    {
        $segments = explode('-', trim($nrc));

        if (count($segments) !== 4) {
            throw InvalidNrcException::for($nrc);
        }

        [$stateId, $townshipId, $typeId, $number] = $segments;

        if (! ctype_digit($stateId) || ! ctype_digit($townshipId) || ! ctype_digit($typeId) || preg_match('/^[0-9]{6}$/', $number) !== 1) {
            throw InvalidNrcException::for($nrc);
        }

        $repository = $this->repository($dbDriven);

        $state = $repository->state((int) $stateId);
        $township = $repository->township((int) $townshipId);
        $type = $repository->type((int) $typeId);

        if ($state === null || $township === null || $type === null || $state->id !== $township->nrc_state_id) {
            throw InvalidNrcException::for($nrc);
        }

        return [$state, $township, $type, $number];
    }

    /**
     * The backend NRC data is read from.
     *
     * @param  bool|null  $dbDriven  Use the database (true) or the JSON file (false); defaults to the "db_driven" config value.
     */
    public function repository(?bool $dbDriven = null): NrcRepository
    {
        return ($dbDriven ?? $this->dbDriven) ? $this->database : $this->json;
    }

    private function toMyanmarDigits(string $number): string
    {
        return strtr($number, ['0' => '၀', '1' => '၁', '2' => '၂', '3' => '၃', '4' => '၄', '5' => '၅', '6' => '၆', '7' => '၇', '8' => '၈', '9' => '၉']);
    }
}
