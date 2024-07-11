<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelperController;
use App\Models\WeatherForecast;
use Illuminate\Support\Facades\Validator;
use App\Services\GeminiService;
use App\Services\WeatherService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OperationController extends HelperController
{
    protected $geminiService;
    protected $weatherService;

    public function __construct(GeminiService $geminiService, WeatherService $weatherService)
    {
        $this->geminiService = $geminiService;
        $this->weatherService = $weatherService;
    }

    public function getWeather(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required',
            'long' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->globalResponse(false, 400, null, $validator->errors());
        }

        $datas = [];

        $result = $this->weatherService->getWeatherPrediction($request->lat, $request->long);
        $forecast = $this->weatherService->getWeatherForecast($request->lat, $request->long);
        $history = $this->weatherService->getWeatherHistory($request->lat, $request->long);
        $topography = $this->weatherService->getTopographicDatas($request->lat, $request->long);

        $datas['prediction'] = $result;
        $datas['forecast'] = $forecast;
        $datas['history'] = $history;
        $datas['topography'] = $topography;

        if($result !== null) {
            $weatherforecast = new WeatherForecast();
            $weatherforecast->location = $result['name'];
            $weatherforecast->forecast = json_encode($forecast);
            $weatherforecast->forecast_date = date('Y-m-d');
            $weatherforecast->forecast_time = date('H:i:s');
            $weatherforecast->save();
        }

        return $this->globalResponse(false, 200, $datas, "Données météorologiques recuperées avec succes");
    }

}
