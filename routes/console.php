<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('economic-map:sync')
    ->everySixHours()
    ->withoutOverlapping(30);

Schedule::command('economic-map:sync-streets')
    ->monthlyOn(1, '02:30')
    ->withoutOverlapping(60);
