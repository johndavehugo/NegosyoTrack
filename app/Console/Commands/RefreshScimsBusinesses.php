<?php

namespace App\Console\Commands;

use App\Services\ScimsJuridicalApiService;
use Illuminate\Console\Command;

class RefreshScimsBusinesses extends Command
{
    protected $signature = 'scims:refresh-businesses';

    protected $description = 'Refresh the cached SCIMS business registry used by barangay filters';

    public function handle(ScimsJuridicalApiService $scims): int
    {
        $count = $scims->refreshAllBusinesses()->count();

        if ($scims->usedStaleCache()) {
            $this->warn("SCIMS refresh failed. Using the last successful registry with {$count} businesses.");

            return self::FAILURE;
        }

        $this->info("Cached {$count} SCIMS businesses.");

        return self::SUCCESS;
    }
}
