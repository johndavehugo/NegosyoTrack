<?php

namespace App\Jobs;

use App\Services\EconomicMapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Refresh the cached SCIMS business snapshot used by the Economic Map.
 */
class SyncEconomicMapData implements ShouldQueue
{
    use Queueable;

    public int $timeout = 900;

    public int $tries = 1;

    public function handle(EconomicMapService $service): void
    {
        $service->sync();
    }
}
