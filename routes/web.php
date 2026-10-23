<?php

use App\Http\Controllers\CalamityReportController;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\Route;

Route::get('/calamity-monitoring/calamities/{calamity}/report', CalamityReportController::class)
    ->middleware(Authenticate::class)
    ->name('calamity-monitoring.report');

// Route::get('/', function () {
//     return view('welcome');
// });
