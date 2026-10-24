<?php

namespace App\Console\Commands;

use App\Services\EconomicMap\StreetDirectory;
use App\Services\EconomicMapService;
use Illuminate\Console\Command;
use Throwable;

class SyncStreetDirectory extends Command
{
    protected $signature = 'economic-map:sync-streets
        {--skip-scims : Build only from OpenStreetMap without refreshing SCIMS names}';

    protected $description = 'Rebuild the searchable San Carlos City street and place directory';

    public function handle(StreetDirectory $directory, EconomicMapService $service): int
    {
        $rows = [];

        if (! $this->option('skip-scims')) {
            $this->info('Reading the cached SCIMS business snapshot...');

            try {
                $rows = $service->businessList();
            } catch (Throwable $exception) {
                $this->error('SCIMS addresses could not be read; keeping the existing directory unchanged.');
                $this->line($exception->getMessage());

                return self::FAILURE;
            }
        }

        $this->info('Fetching streets, subdivisions, and place names from OpenStreetMap...');

        try {
            $count = $directory->refresh($rows);
        } catch (Throwable $exception) {
            $this->error('Street directory sync failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Wrote {$count} searchable street and place entries to resources/data/san-carlos-streets.json.");

        return self::SUCCESS;
    }
}
