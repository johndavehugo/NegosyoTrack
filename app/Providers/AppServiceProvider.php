<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentColor::register([
            'blue-badge-1' => [
                50 => 'oklch(0.96 0.06 245)',
                100 => 'oklch(0.91 0.11 245)',
                400 => 'oklch(0.76 0.20 245)',
                500 => 'oklch(0.68 0.23 245)',
                600 => 'oklch(0.58 0.22 245)',
                700 => 'oklch(0.48 0.19 245)',
                900 => 'oklch(0.32 0.13 245)',
                950 => 'oklch(0.22 0.09 245)',
            ],

            'blue-badge-2' => [
                50 => 'oklch(0.93 0.08 250)',
                100 => 'oklch(0.85 0.14 250)',
                400 => 'oklch(0.60 0.26 250)',
                500 => 'oklch(0.52 0.28 250)',
                600 => 'oklch(0.44 0.25 250)',
                700 => 'oklch(0.36 0.21 250)',
                900 => 'oklch(0.24 0.14 250)',
                950 => 'oklch(0.16 0.09 250)',
            ],

            'blue-badge-3' => [
                50 => 'oklch(0.90 0.10 255)',
                100 => 'oklch(0.80 0.16 255)',
                400 => 'oklch(0.46 0.23 255)',
                500 => 'oklch(0.38 0.22 255)',
                600 => 'oklch(0.31 0.19 255)',
                700 => 'oklch(0.25 0.16 255)',
                900 => 'oklch(0.16 0.11 255)',
                950 => 'oklch(0.11 0.07 255)',
            ],

            'blue-badge-4' => [
                50 => 'oklch(0.87 0.12 260)',
                100 => 'oklch(0.76 0.15 260)',
                400 => 'oklch(0.34 0.18 260)',
                500 => 'oklch(0.26 0.16 260)',
                600 => 'oklch(0.20 0.14 260)',
                700 => 'oklch(0.15 0.11 260)',
                900 => 'oklch(0.09 0.07 260)',
                950 => 'oklch(0.06 0.04 260)',
            ],
        ]);
    }
}
