<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelperController;
use App\Models\FloodReport;
use App\Models\WeatherForecast;
use Illuminate\Support\Facades\Validator;
use App\Services\GeminiService;
use App\Services\LocationService;
use App\Services\WeatherService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OperationController extends HelperController
{
    protected $geminiService;
    protected $weatherService;
    protected $locationService;

    public function __construct(GeminiService $geminiService, WeatherService $weatherService, LocationService $locationService)
    {
        $this->geminiService = $geminiService;
        $this->weatherService = $weatherService;
        $this->locationService = $locationService;
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

    public function saveReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'description' => 'required|string',
            'photo' => 'nullable|image|max:2048', // Optionnel: validation de la photo
            'location' => 'required|string',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->globalResponse(false, 400, null, $validator->errors());
        }

        $report = FloodReport::where('user_id', $request->user_id)->where('latitude', $request->latitude)->where('longitude', $request->longitude)->first();

        if($report){
            return $this->globalResponse(false, 401, null, "Le signalement existe déjà");
        }

        $floodReport = FloodReport::create([
            'user_id' => $request->user_id,
            'description' => $request->description,
            'photo' => $request->file('photo')->store('flood_reports', 'public'), // Stocker la photo
            'location' => $request->location,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        if($floodReport){
            return $this->globalResponse(false, 200, $floodReport, "Signalement enregistré avec succes");
        }else{
            return $this->globalResponse(true, 400, null, "Erreur lors de l'enregistrement du signalement");
        }

    }

    

}
