<?php

namespace App\Jobs;

use App\Http\Controllers\api\WeatherController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateWeatherData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $location;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($location = 'Perth')
    {
        $this->location = $location;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $weatherController = new WeatherController();

        try {
            $weatherController->updateWeatherData($this->location);
        } catch (\Exception $e) {
            Log::error('Failed to update weather data', ['location' => $this->location, 'error' => $e->getMessage()]);
        }
    }
}
