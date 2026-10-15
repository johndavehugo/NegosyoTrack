<?php

use App\Http\Controllers\PublicPriceController;
use Illuminate\Support\Facades\Route;

Route::get('/sccpresyo', [PublicPriceController::class, 'index'])->name('public.price-watch');
Route::get('/sccpresyo/detail/{variant}', [PublicPriceController::class, 'detail'])->name('public.price-watch.detail');

// Route::get('/', function () {
//     return view('welcome');
// });
