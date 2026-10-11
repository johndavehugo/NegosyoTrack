<?php

use App\Livewire\PriceList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SCCPresyo — Public Price Monitoring
| Standalone Blade + Livewire page, zero Filament dependency.
|--------------------------------------------------------------------------
*/
Route::get('/sccpresyo', PriceList::class)->name('sccpresyo');
