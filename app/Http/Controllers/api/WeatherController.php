<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
*  @OA\Schema(
*     schema="Weather",
*     title="Weather",
*     description="Get OpenWeather Data",
* )
*/
class WeatherController extends Controller
{
    
    /**
     * @OA\Get(
     *     path="/api/weather",
     *     summary="Get weather data by location",
     *     description="Fetch weather information from OpenWeather API",
     *     tags={"Weather"},
     *     @OA\Parameter(
     *         name="location",
     *         in="query",
     *         description="Location name to fetch weather data for",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="Perth"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Weather data for the given location",
     *         @OA\JsonContent(ref="#/components/schemas/Weather")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid location provided"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Location not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unable to fetch weather data"
     *     )
     * )
     */
    public function index(Request $request) {
        $location = $request->query('location', 'Perth');

        if (empty($location) || !is_string($location)) {
            return response()->json(['error' => 'Invalid location provided.'], 400);
        }

        try {
            $weatherData = $this->fetchWeatherData($location);
            
            return response()->json($weatherData, 200);

        } catch (\Exception $e) {
     
            Log::error('Error fetching weather data', [
                'exception' => $e->getMessage(),
                'location' => $location,
            ]);
            
            return response()->json([
                'error' => 'An error occurred while fetching weather data.',
                'message' => env('APP_ENV') === 'production' ? 'Please try again later.' : $e->getMessage(),
            ], 500);
        }
    }

    public function updateWeatherData(string $location) {
        return $this->fetchWeatherData($location);
    }

    /**
     * Fetch weather data from OpenWeather API
     *
     * @param string $location
     * @return array
     * @throws \Exception
     */
    protected function fetchWeatherData(string $location): array {

        $apiKey = env('WEATHER_API_KEY');

        if (empty($apiKey)) {
            throw new \Exception('API Key is missing');
        }

        $url = config('services.weather.base_url') . '/data/2.5/weather';
        
        $response = Http::get($url, [
            'q' => $location,
            'appid' => $apiKey,
            'units' => 'metric',
        ]);

        if ($response->failed()) {
            $errorResponse = $response->json();

            if ($response->status() === 401) {
                Log::error('Invalid API Key', ['location' => $location, 'error' => $errorResponse]);
                throw new \Exception('Invalid API key. Please check your API key and try again.');
            }

            if ($response->status() === 404) {
                Log::error('Location not found', ['location' => $location]);
                throw new \Exception('Location not found.');
            }

            Log::error('Weather API error', ['location' => $location, 'error' => $errorResponse]);
            throw new \Exception('Unable to fetch weather data. Please try again later.');
        }


        // return the data 
        return $response->json();
    }
}
