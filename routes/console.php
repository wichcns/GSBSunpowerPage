<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('crm:line-reminders')->dailyAt('08:00')->timezone('Asia/Bangkok')->withoutOverlapping();
Schedule::command('crm:line-outbox')->everyMinute()->withoutOverlapping();
