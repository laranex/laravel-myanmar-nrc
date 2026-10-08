<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Laranex\LaravelMyanmarNRC\Models\State;
use Laranex\LaravelMyanmarNRC\Models\Township;
use Laranex\LaravelMyanmarNRC\Models\Type;
use Laranex\LaravelMyanmarNRC\Repositories\JsonNrcRepository;

class SeedMyanmarNrcCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mm-nrc:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Replace the NRC states, townships and types in the database with the data from the JSON file';

    /**
     * Execute the console command.
     */
    public function handle(JsonNrcRepository $json): int
    {
        $this->info(sprintf('Loading NRC data from [%s].', $json->path()));

        $types = $json->types();
        $states = $json->states();
        $townships = $json->townships();

        $this->warn('Deleting the existing NRC data from the database.');

        Schema::disableForeignKeyConstraints();

        Township::query()->truncate();
        State::query()->truncate();
        Type::query()->truncate();

        Schema::enableForeignKeyConstraints();

        foreach (array_chunk($types, 100) as $chunk) {
            Type::query()->insert($chunk);
        }

        foreach (array_chunk($states, 100) as $chunk) {
            State::query()->insert($chunk);
        }

        foreach (array_chunk($townships, 100) as $chunk) {
            Township::query()->insert($chunk);
        }

        $this->info(sprintf('Seeded %d types, %d states and %d townships.', count($types), count($states), count($townships)));

        return self::SUCCESS;
    }
}
