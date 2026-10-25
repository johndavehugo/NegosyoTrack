<?php

namespace App\Providers;

use App\Console\Commands\PruneIncidentDrafts;
use App\Console\Commands\RefreshScimsBusinesses;
use App\Services\CalamityMonitoring\ScimsBusinessRegistry;
use App\Services\CalamityMonitoring\SnapshotCalamityFeed;
use App\Services\EconomicMap\CalamityFeed;
use App\Services\ScimsJuridicalApiService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class CalamityMonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ScimsBusinessRegistry::class);
        $this->app->alias(ScimsBusinessRegistry::class, ScimsJuridicalApiService::class);
        $this->app->bind(CalamityFeed::class, SnapshotCalamityFeed::class);
    }

    public function boot(): void
    {
        if (! $this->app->routesAreCached()) {
            Route::middleware('web')->group(base_path('routes/calamity-monitoring.php'));
        }

        if ($this->app->runningInConsole()) {
            Schedule::command(RefreshScimsBusinesses::class)->daily();
            Schedule::command(PruneIncidentDrafts::class)->hourly();
        }
    }
}
