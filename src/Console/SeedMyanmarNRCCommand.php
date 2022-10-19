<?php

namespace laranex\LaravelMyanmarNRC\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use laranex\LaravelMyanmarNRC\Data\MyanmarNRCJsonHandler;
use laranex\LaravelMyanmarNRC\Models\State;
use laranex\LaravelMyanmarNRC\Models\Township;
use laranex\LaravelMyanmarNRC\Models\Type;

class SeedMyanmarNRCCommand extends Command
{
    protected $signature = 'mm-nrc:seed';

    protected $description = 'Delete the previous data from Myanmar NRC tables, Seed the data again';

    public function handle()
    {
        $nrcData = new MyanmarNRCJsonHandler();

        $this->info('Loading and seeding NRCs from configs/laravel-myanmar-nrc');

        $this->warn('Deleting NRCs from database');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Type::truncate();
        State::truncate();
        Township::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->warn('Deleting NRCs from database is completed');

        Type::insert($nrcData->types->toArray());
        State::insert($nrcData->states->toArray());
        Township::insert($nrcData->townships->toArray());

        $this->info('NRCs from configs/laravel-myanmar-nrc were seeded into database');
    }
}
