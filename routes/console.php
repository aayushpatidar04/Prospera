<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('fetch:traded-stocks')->everyMinute()->between('9:00', '16:00')->weekdays();
Schedule::command('traded-stocks:clear')->dailyAt('06:00');