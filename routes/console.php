<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

if (config('demo.enabled')) {
    $demoResetSchedule = (string) config('demo.reset_schedule', '0 */6 * * *');

    if ($demoResetSchedule !== '') {
        Schedule::command('demo:reset')
            ->cron($demoResetSchedule)
            ->withoutOverlapping();
    }
}
