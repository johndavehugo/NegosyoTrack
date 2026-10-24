<?php

namespace App\Providers;

use App\Console\Commands\PruneIncidentDrafts;
use App\Console\Commands\RefreshScimsBusinesses;
use App\Services\ScimsJuridicalApiService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\ServiceProvider;

class CalamityMonitoringServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ScimsJuridicalApiService::class);
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
