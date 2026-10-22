<?php

<<<<<<< HEAD
use App\Livewire\PriceList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SCCPresyo — Public Price Monitoring
| Standalone Blade + Livewire page, zero Filament dependency.
|--------------------------------------------------------------------------
*/
Route::get('/sccpresyo', PriceList::class)->name('sccpresyo');
=======
use App\Http\Controllers\PublicPriceController;
use Illuminate\Support\Facades\Route;

Route::get('/sccpresyo', [PublicPriceController::class, 'index'])->name('public.price-watch');
Route::get('/sccpresyo/detail/{variant}', [PublicPriceController::class, 'detail'])->name('public.price-watch.detail');

// Route::get('/', function () {
//     return view('welcome');
// });
>>>>>>> df62f001a3065ad8f6b20b54636559892424e680
