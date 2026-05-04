<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent";

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    /**
     * Filtro di sicurezza SUPER SEVERO: determina se l'immagine è un documento reale.
     */
    public function checkIfIsDocument($filePath, $docType, $mimeType): bool
    {
        try {
            $imagePath = storage_path('app/public/' . $filePath);
            
            if (!file_exists($imagePath)) {
                return false;
            }

            $imageData = base64_encode(file_get_contents($imagePath));
            
            $target = str_contains($docType, 'id_') 
                ? "un VERO documento di identità (carta d'identità, passaporto o patente)" 
                : "una VERA tessera sanitaria o tesserino del codice fiscale italiano in plastica o cartaceo ufficiale";

            $prompt = "Agisci come un ispettore doganale antifrode. Analizza l'immagine e stabilisci se è $target. 
            
            CRITERI DI SCARTO ASSOLUTO (Rispondi false se noti uno di questi):
            1. È un foglio di carta bianco scritto a mano o stampato non ufficialmente.
            2. È una foto dello schermo di un computer o di un altro telefono.
            3. È un selfie o la foto di una persona senza il documento.
            
            CRITERI DI APPROVAZIONE:
            - Il documento può essere una tessera plastificata (con chip, ologrammi, MRZ).
            - IMPORTANTE: Il documento PUÒ essere una vecchia carta d'identità cartacea italiana (formato a libretto aperto, sfondo con filigrana ministeriale rosata/verde e timbri a inchiostro). In questo caso DEVE essere approvato come valido.
            - Sono accettate le scansioni piane (flatbed scans) purché ritraggano un documento ufficiale reale.";

            $response = Http::post("{$this->apiUrl}?key={$this->apiKey}", [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt],
                            [
                                "inline_data" => [
                                    "mime_type" => $mimeType,
                                    "data" => $imageData
                                ]
                            ]
                        ]
                    ]
                ],
                // FORZATURA STRUTTURA JSON: Gemini non può più sbagliare formato
                "generationConfig" => [
                    "temperature" => 0.1, // Abbassiamo la "creatività" dell'IA
                    "response_mime_type" => "application/json",
                    "response_schema" => [
                        "type" => "OBJECT",
                        "properties" => [
                            "is_document" => ["type" => "BOOLEAN"],
                            "confidence_score" => ["type" => "NUMBER"],
                            "reason" => ["type" => "STRING"]
                        ],
                        "required" => ["is_document", "confidence_score", "reason"]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                $result = json_decode($rawText, true);
                
                if (!is_array($result)) {
                    return false;
                }

                if (isset($result['is_document']) && $result['is_document'] === false) {
                    Log::channel('single')->info("Documento bloccato da Gemini. Motivo: " . ($result['reason'] ?? 'ND'));
                }

                // Deve essere considerato documento con almeno 85% di certezza
                return (
                    isset($result['is_document']) && 
                    $result['is_document'] === true &&
                    isset($result['confidence_score']) && 
                    $result['confidence_score'] >= 0.85
                );
            }

            Log::error("Errore API Gemini: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Eccezione GeminiService: " . $e->getMessage());
            return false;
        }
    }

   public function scanDocument($filePath)
    {
        $imagePath = storage_path('app/public/' . $filePath);
        $imageData = base64_encode(file_get_contents($imagePath));
        
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