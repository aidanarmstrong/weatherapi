<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class WeatherControllerTest extends TestCase
{
    /**
     * Test successful weather data retrieval
     *
     * @return void
     */
    public function test_successful_weather_data_retrieval()
    {
        Http::fake([
            '*/data/2.5/weather*' => Http::response([
                'weather' => [['description' => 'clear sky']],
                'main' => ['temp' => 25],
                'name' => 'Perth',
            ], 200),
        ]);

        $response = $this->getJson('/api/weather?location=Perth');

        $response->assertStatus(200)
            ->assertJson([
                'weather' => [['description' => 'clear sky']],
                'main' => ['temp' => 25],
                'name' => 'Perth',
            ]);
    }

    /**
     * Test weather API general error
     *
     * @return void
     */
    public function test_weather_api_general_error()
    {
        Http::fake([
            '*/data/2.5/weather*' => Http::response([
                'message' => 'Internal server error'
            ], 500),
        ]);

        $response = $this->getJson('/api/weather?location=Perth');

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'An error occurred while fetching weather data.',
            ]);
    }
}
