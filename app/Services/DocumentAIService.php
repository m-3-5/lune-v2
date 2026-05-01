<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class DocumentAIService
{
    /**
     * Invia la Carta d'Identità a Google Document AI
     */
    public function analyzeIdentityDocument(UploadedFile $front, ?UploadedFile $back = null): array
    {
        // TODO: Qui integreremo il client Google Cloud Document AI
        Log::info("Inviato documento d'identità a Google Document AI per analisi.");

        // Simuliamo una risposta di successo da Google per testare la UI
        return [
            'status' => 'success', // Può essere: 'success', 'error', 'unreadable'
            'confidence' => 0.98,
            'extracted_data' => [
                'document_type' => 'identity_card',
                'first_name' => 'Mario',
                'last_name' => 'Rossi',
                'expiration_date' => '2030-01-01',
                'is_valid' => true // Google ci confermerà se non è scaduto
            ],
            'message' => 'Documento validato con successo.'
        ];
    }

    /**
     * Invia il Codice Fiscale / Tessera Sanitaria
     */
    public function analyzeTaxCode(UploadedFile $front, ?UploadedFile $back = null): array
    {
        // TODO: Qui integreremo il client Google Cloud Document AI
        Log::info("Inviato codice fiscale a Google Document AI per analisi.");

        // Simuliamo la risposta
        return [
            'status' => 'success',
            'confidence' => 0.99,
            'extracted_data' => [
                'tax_code' => 'RSSMRA80A01H501U',
                'is_valid' => true
            ],
            'message' => 'Codice fiscale validato.'
        ];
    }
}