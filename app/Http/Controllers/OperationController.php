<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelperController;
use App\Models\FloodReport;
use App\Models\FloodZone;
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

    public function getReports()
    {
        $reports = FloodReport::all();

        return $this->globalResponse(false, 200, $reports, "Signalements récuperés avec succès");
    }

    public function getUserReports($id)
    {
        $reports = FloodReport::where('user_id', $id)->get();

        return $this->globalResponse(false, 200, $reports, "Signalements récuperés avec succès");
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

    public function getFloodZones()
    {
        $zones = FloodZone::all();

        return $this->globalResponse(false, 200, $zones, "Zones d'eau récupérées avec succes");
    }

    public function actualizeFloodZone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'location' => 'required|string',
            'latitude' => 'required|string',
            'longitude' => 'required|string',
            'risk_level' => 'required|string',
            'historical_data' => 'nullable',
        ]);

        if ($validator->fails()) {
            return $this->globalResponse(false, 400, null, $validator->errors());
        }

        $floodZone = FloodZone::where('latitude', $request->latitude)
                              ->where('longitude', $request->longitude)
                              ->first();

        if ($floodZone) {
            $floodZone->location = $request->location;
            $floodZone->risk_level = $request->risk_level;
            $floodZone->historical_data = $request->historical_data;
            $floodZone->save();

            return $this->globalResponse(false, 200, $floodZone, "Zone actualisée avec succes");
        } else {
            // Créer une nouvelle entrée pour la zone
            $floodZone = FloodZone::create([
                'location' => $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'risk_level' => $request->risk_level,
                'historical_data' => $request->historical_data,
            ]);

            if ($floodZone) {
                return $this->globalResponse(false, 200, $floodZone, "Zone enregistrée avec succes");
            } else {
                return $this->globalResponse(true, 400, null, "Erreur lors de l'enregistrement de la zone");
            }
        }
    }

}
