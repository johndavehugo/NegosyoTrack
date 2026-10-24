<?php

use App\Providers\AppServiceProvider;
use App\Providers\CalamityMonitoringServiceProvider;
use App\Providers\Filament\NegosyotrackPanelProvider;

return [
    AppServiceProvider::class,
    CalamityMonitoringServiceProvider::class,
    NegosyotrackPanelProvider::class,
];
