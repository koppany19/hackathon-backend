<?php

use App\Services\DailyTaskService;
use App\Services\GroupMatchingService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    app(DailyTaskService::class)->generateForAll();
})->dailyAt('00:01');

Schedule::command('app:generate-group-tasks')->dailyAt('00:05');

Artisan::command('app:run-daily-cycle', function () {
    $this->info('Generating individual daily tasks...');
    app(DailyTaskService::class)->generateForAll();
    $this->info('Done. Generating AI group tasks...');
    app(GroupMatchingService::class)->run();
    $this->info('Daily cycle complete.');
})->purpose('Manually trigger the full daily task generation cycle (individual + group)');
