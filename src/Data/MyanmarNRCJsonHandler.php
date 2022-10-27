<?php

namespace Laranex\LaravelMyanmarNRC\Data;

use Illuminate\Support\Collection;
use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;

class MyanmarNRCJsonHandler
{
    public Collection $types;

    public Collection $states;

    public Collection $townships;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->readJsonData();
    }

    /**
     * @throws \Exception
     */
    private function readJsonData(): void
    {
        $jsonFile = config('laravel-myanmar-nrc.json_file');
        $json = file_get_contents($jsonFile, FILE_USE_INCLUDE_PATH);

        $nrcCollection = json_decode($json, true);

        if (! isset($nrcCollection['types']) || ! isset($nrcCollection['states'])) {
            throw  new \Exception('Invalid Json File');
        }

        $this->types = collect($nrcCollection['types']);
        $this->states = collect();
        $this->townships = collect();

        collect($nrcCollection['states'])
            ->each(function ($state) {
                $this->states->push(collect($state)->only(['id', 'code', 'code_mm', 'name', 'name_mm'])->toArray());

                collect($state['townships'])->each(function ($township) use ($state) {
                    $township['nrc_state_id'] = $state['id'];
                    $this->townships->push($township);
                });
            });
    }

    public function getState($stateId): State
    {
        return $this->castASModel($this->states->where('id', $stateId)->firstOrFail(), State::class);
    }

    public function getTownship($townshipId): Township
    {
        return $this->castASModel($this->townships->where('id', $townshipId)->firstOrFail(), Township::class);
    }

    public function getType($typeId): Type
    {
        return $this->castASModel($this->types->where('id', $typeId)->firstOrFail(), Type::class);
    }

    private function castASModel(array $raw, string $model)
    {
        $model = new $model;
        if ($model instanceof Township) {
            $model->nrc_state_id = $raw['nrc_state_id'];
        }

        if ($model instanceof State) {
            $model->code = $raw['id'];
        } else {
            $model->code = $raw['code'];
        }

        $model->id = $raw['id'];
        $model->code_mm = $raw['code_mm'];
        $model->name = $raw['name'];
        $model->name_mm = $raw['name_mm'];

        return $model;
    }
}
