<?php

namespace App\Services;

use App\Http\Controllers\Helpers\HelperController;
use Illuminate\Support\Facades\Http;

class GeminiService extends HelperController
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function getFloodPrediction($data)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.0-pro-latest:generateContent?key=' . $this->apiKey;

        $prompt = [
            'contents' => [
                [
                    'parts' => [
                        [
                            // 'text' => 'Analyze the following flood report and extract important information: '. $data
                            'text' => "Analyse ces données météorologiques et extrayez les informations importantes pour prédire le risque d'inondation: ". $data
                        ]
                    ]
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($url, $prompt);

        dd($response->json());

        if ($response->failed()) {
            return $this->globalResponse(true, 500, null, "Une erreur est survenue");
        }

        // return $response->json();
        $data = $response->json();
        return $this->globalResponse(false, 200, $data, "Réponse récupérée avec succès");
    }
}
