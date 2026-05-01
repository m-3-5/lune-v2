<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GeminiService
{
    protected $apiKey;
    // Usiamo il modello Flash per la sua velocità nel rispondere all'ospite
    protected $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent";

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function scanDocument($filePath)
    {
        $imagePath = storage_path('app/public/' . $filePath);
        $imageData = base64_encode(file_get_contents($imagePath));

        // Prepariamo l'istruzione per l'IA
        $prompt = "Sei un esperto di documenti. Analizza questa immagine e restituisci SOLO un JSON con: 
                   first_name, last_name, document_type, expiry_date (YYYY-MM-DD). 
                   Se l'immagine non è un documento, restituisci un campo 'error'.";

        $response = Http::post("{$this->apiUrl}?key={$this->apiKey}", [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        [
                            "inline_data" => [
                                "mime_type" => "image/jpeg",
                                "data" => $imageData
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        return $response->json();
    }
}