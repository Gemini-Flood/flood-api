<?php

namespace App\Services;

use App\Http\Controllers\Helpers\HelperController;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class WeatherService extends HelperController
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('WEATHER_API_KEY');
    }

    public function getWeatherPrediction($lat, $long)
    {
        $url = "https://api.openweathermap.org/data/2.5/weather?lat=".$lat."&lon=".$long."&APPID=".$this->apiKey."&units=metric";

        $response = Http::get($url);

        if($response->failed()) {
            $data = null;
            return $data;
        }

        $data = $response->json();
        return $data;
    }

    public function getWeatherForecast($lat, $long)
    {
        $url = "https://api.openweathermap.org/data/2.5/forecast?lat=".$lat."&lon=".$long."&APPID=".$this->apiKey."&units=metric";

        $response = Http::get($url);

        if($response->failed()) {
            $data = null;
            return $data;
        }

        $data = $response->json();
        return $data;
    }

    public function getWeatherHistory($lat, $long)
    {
        $today = Carbon::today()->format('Y-m-d');
        $twoMonthsAgo = Carbon::today()->subMonths(2)->format('Y-m-d');

        $url = "https://archive-api.open-meteo.com/v1/archive?latitude=".$lat."&longitude=".$long."&start_date=".$twoMonthsAgo."&end_date=".$today."&hourly=precipitation,rain,soil_temperature_0_to_7cm,soil_temperature_7_to_28cm,soil_temperature_28_to_100cm,soil_temperature_100_to_255cm,soil_moisture_0_to_7cm,soil_moisture_7_to_28cm,soil_moisture_28_to_100cm,soil_moisture_100_to_255cm&models=best_match";

        $response = Http::get($url);

        if($response->failed()) {
            $data = null;
            return $data;
        }

        $data = $response->json();
        return $data;
    }

    public function getTopographicDatas($lat, $long)
    {
        $url = "https://api.opentopodata.org/v1/srtm90m?locations=".$lat.",".$long;

        $response = Http::get($url);

        if($response->failed()) {
            $data = null;
            return $data;
        }

        $result = $response->json();
        
        $data = $result["results"][0];

        return $data;
    }
}
