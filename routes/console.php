<?php

use App\Jobs\UpdateWeatherData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Artisan::command('schedule:weather-update', function () {
//     UpdateWeatherData::dispatch('Perth');
// })->hourly();

app()->booted(function () {
    $schedule = app(Schedule::class);
    $schedule->command('schedule:weather-update')
        ->hourly()
        ->description('Fetch weather data for Perth')
        ->appendOutputTo(storage_path('logs/weather-update.log'));
});