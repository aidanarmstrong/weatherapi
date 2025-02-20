<?php

namespace App\Providers;

use App\Console\Commands\DispatchWelcomeEmail;
use App\Jobs\UpdateWeatherData;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Schedule $schedule): void
    {
        $this->commands([
            DispatchWelcomeEmail::class,
        ]);

        $schedule->job(new UpdateWeatherData())->hourly();
    }
}
