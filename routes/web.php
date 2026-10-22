<?php

use App\Http\Controllers\PublicPriceController;
use Illuminate\Support\Facades\Route;

Route::get('/sccpresyo', [PublicPriceController::class, 'index'])->name('sccpresyo.index');
Route::get('/sccpresyo/detail/{variant}', [PublicPriceController::class, 'detail'])->name('sccpresyo.detail');

// Route::get('/', function () {
//     return view('welcome');
// });
