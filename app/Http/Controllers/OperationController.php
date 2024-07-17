<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Helpers\HelperController;
use App\Models\Alert;
use App\Models\FloodReport;
use App\Models\FloodZone;
use App\Models\WeatherForecast;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;
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
    protected $firebaseService;

    public function __construct(GeminiService $geminiService, WeatherService $weatherService, LocationService $locationService, FirebaseService $firebaseService)
    {
        $this->geminiService = $geminiService;
        $this->weatherService = $weatherService;
        $this->locationService = $locationService;
        $this->firebaseService = $firebaseService;
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
        $date = Carbon::now();
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

        if($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $picture = $date->getTimestamp().'_'.$photo->getClientOriginalExtension();
            $photo->move(base_path('../public/images/flood_reports'), $picture);
        }

        $floodReport = FloodReport::create([
            'user_id' => $request->user_id,
            'description' => $request->description,
            'image' => $request->hasFile('photo') ? 'flood_reports'.'/'.$picture : null, // Stocker la photo
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
        $success = false;
        $date = Carbon::now();
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

            $success = true;
        } else {
            $floodZone = FloodZone::create([
                'location' => $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'risk_level' => $request->risk_level,
                'historical_data' => $request->historical_data,
            ]);

            if ($floodZone) {
                $success = true;
            }
        }

        $existingAlert = Alert::where('flood_zone_id', $floodZone->id)
                            ->where('risk_level', $request->risk_level)
                            ->where('expires_at', '>', $date)
                            ->first();

        if (!$existingAlert) {
            $alertMessage = $this->generateAlertMessage($request->risk_level);

            $alert = Alert::create([
                'flood_zone_id' => $floodZone->id,
                'title' => "Alerte d'inondation",
                'message' => $alertMessage,
                'risk_level' => $request->risk_level,
                'expires_at' => $date->addDays(3),
            ]);

            $users = $this->locationService->getUsersInZone($floodZone->latitude, $floodZone->longitude);
            foreach ($users as $user) {
                $this->firebaseService->sendPushNotification($alert->title, $alert->message, $user->fcm_token);
            }
        }


        if($success){
            return $this->globalResponse(false, 200, $floodZone, "Zone mise à jour avec succes");
        }else{
            return $this->globalResponse(true, 400, null, "Erreur lors de la mise à jour de la zone");
        }
    }

    private function generateAlertMessage($riskLevel)
    {
        switch ($riskLevel) {
            case "faible":
                return "Risque faible d'inondation. Soyez vigilant et suivez les prévisions météorologiques.";
            case "modéré":
                return "Risque modéré d'inondation. Prenez des précautions et préparez-vous à une éventuelle évacuation.";
            case "élevé":
                return "Risque élevé d'inondation. Évacuez la zone immédiatement et suivez les instructions des autorités locales.";
            case "extrême":
                return "Risque extrême d'inondation. Danger imminent. Évacuez immédiatement et cherchez un abri sûr.";
            default:
                return "Risque inconnu. Restez vigilant et suivez les prévisions météorologiques.";
        }
    }

}
