<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the reports:run-scheduled command to run every 5 minutes
Schedule::command('reports:run-scheduled')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();
