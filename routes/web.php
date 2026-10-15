<?php

use App\Http\Controllers\PublicPriceController;
use Illuminate\Support\Facades\Route;

Route::get('/sccpresyo', [PublicPriceController::class, 'index'])->name('public.price-watch');

// Route::get('/', function () {
//     return view('welcome');
// });
