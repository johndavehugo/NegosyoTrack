<?php

namespace App\Console\Commands;

use App\Services\EconomicMapService;
use Illuminate\Console\Command;
use Throwable;

class SyncEconomicMap extends Command
{
    protected $signature = 'economic-map:sync';

    protected $description = 'Refresh the SCIMS business snapshot used by the Economic Map';

    public function handle(EconomicMapService $service): int
    {
        $this->info('Fetching businesses from SCIMS...');

        try {
            $count = $service->sync();
        } catch (Throwable $exception) {
            $this->error('SCIMS sync failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Cached {$count} businesses for the Economic Map.");

        return self::SUCCESS;
    }
}
