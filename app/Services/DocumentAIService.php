<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\RawDocument;
use Google\Cloud\DocumentAI\V1\ProcessRequest;
use Illuminate\Support\Facades\Log;

class DocumentAIService
{
    /**
     * Invia la Carta d'Identità a Google Document AI
     */
    public function analyzeIdentityDocument(UploadedFile $front, ?UploadedFile $back = null): array
    {
        return $this->processWithGoogleAI($front, 'identity');
    }

    /**
     * Invia il Codice Fiscale / Tessera Sanitaria
     */
    public function analyzeTaxCode(UploadedFile $front, ?UploadedFile $back = null): array
    {
        // Usiamo lo stesso processore, è intelligente abbastanza per estrarre testo da qualsiasi documento
        return $this->processWithGoogleAI($front, 'tax_code');
    }

    /**
     * Motore centrale che comunica con Google Cloud
     */
    private function processWithGoogleAI(UploadedFile $file, string $type): array
    {
        try {
            $projectId = env('GOOGLE_CLOUD_PROJECT_ID');
            $location = env('GOOGLE_DOCUMENT_AI_LOCATION', 'eu');
            $processorId = env('GOOGLE_DOCUMENT_AI_PROCESSOR_ID');

            $client = new DocumentProcessorServiceClient();
            $name = $client->processorName($projectId, $location, $processorId);

            $content = file_get_contents($file->getRealPath());
            $rawDocument = (new RawDocument())
                ->setContent($content)
                ->setMimeType($file->getMimeType());

            $request = (new ProcessRequest())
                ->setName($name)
                ->setRawDocument($rawDocument);

            $response = $client->processDocument($request);
            $document = $response->getDocument();
            $client->close();

            $extractedData = [];
            // Estrapoliamo i dati che Google è riuscito a leggere
            foreach ($document->getEntities() as $entity) {
                $entityType = $entity->getType();
                $text = $entity->getMentionText();

                if (in_array($entityType, ['First Name', 'Given Names'])) $extractedData['first_name'] = $text;
                if (in_array($entityType, ['Last Name', 'Family Name'])) $extractedData['last_name'] = $text;
            }

            // Logica di successo: Per i documenti di identità vogliamo almeno Nome e Cognome
            // Per il CF, ci basta che non sia andato in errore e abbia letto qualcosa.
            $isSuccess = false;
            if ($type === 'identity') {
                $isSuccess = !empty($extractedData['first_name']) && !empty($extractedData['last_name']);
            } else {
                $isSuccess = count($document->getEntities()) > 0 || !empty($document->getText());
            }

            return [
                'status' => $isSuccess ? 'success' : 'error',
                'extracted_data' => $extractedData,
                'message' => $isSuccess ? 'Documento validato.' : 'Dati non leggibili. Verrà controllato manualmente da Serenella.'
            ];

        } catch (\Exception $e) {
            Log::error('Errore Document AI: ' . $e->getMessage());
            return [
                'status' => 'error',
                'extracted_data' => [],
                'message' => 'Errore di connessione a Google AI.'
            ];
        }
    }
}