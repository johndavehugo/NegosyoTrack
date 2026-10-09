<?php

use App\Http\Controllers\Api\MsmeController;
use Illuminate\Support\Facades\Route;

// Msme shits
Route::get('/msme', MsmeController::class.'@index');
Route::post('msme', MsmeController::class.'@store')->name('msme.store');
Route::put('/msme/employer/{employer:entity_no}', MsmeController::class.'@updateEmployer')->name('msme.employer.update');
Route::put('/msme/juridical/{juridical:entity_no}', MsmeController::class.'@updateJuridical')->name('msme.juridical.update');
Route::put('/msme/address/{address:id}', MsmeController::class.'@updateAddress')->name('msme.address.update');
Route::patch('/msme/status/{juridical:entity_no}', MsmeController::class.'@changeStatus')->name('msme.status');
Route::patch('/msme/renew/{juridical:entity_no}', MsmeController::class.'@renew')->name('msme.renew');
Route::patch('/msme/location/{address:id}', MsmeController::class.'@setLocation')->name('msme.location');
