<?php

declare(strict_types=1);

namespace Laranex\LaravelMyanmarNRC\Console\Commands;

use Illuminate\Console\Command;
use Laranex\LaravelMyanmarNRC\Repositories\DatabaseNrcRepository;

class ClearMyanmarNrcCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mm-nrc:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Forget the cached NRC states, townships and types so the next lookup reads the database again';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseNrcRepository $database): int
    {
        $database->flush();

        $this->info('The NRC cache was cleared.');

        return self::SUCCESS;
    }
}
