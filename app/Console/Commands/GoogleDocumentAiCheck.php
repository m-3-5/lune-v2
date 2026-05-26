<?php

namespace App\Console\Commands;

use App\Support\GoogleCredentials;
use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\GetProcessorRequest;
use Illuminate\Console\Command;

class GoogleDocumentAiCheck extends Command
{
    protected $signature = 'jlune:google-check';

    protected $description = 'Verifica file credenziali Google e configurazione Document AI';

    public function handle(): int
    {
        $diag = GoogleCredentials::diagnostics();

        $this->info('Configurazione Google Cloud / Document AI');
        $this->table(
            ['Chiave', 'Valore'],
            [
                ['GOOGLE_APPLICATION_CREDENTIALS (.env)', $diag['configured'] ?: '(vuoto)'],
                ['Percorso risolto', $diag['resolved'] ?: '(non risolvibile)'],
                ['File esiste', $diag['exists'] ? 'sì' : 'NO'],
                ['File leggibile', $diag['readable'] ? 'sì' : 'NO'],
                ['GOOGLE_CLOUD_PROJECT_ID', $diag['project_id'] ?: '(vuoto)'],
                ['GOOGLE_DOCUMENT_AI_LOCATION', $diag['location'] ?: '(vuoto)'],
                ['GOOGLE_DOCUMENT_AI_PROCESSOR_ID', $diag['processor_id'] ?: '(vuoto)'],
            ]
        );

        if (! $diag['readable']) {
            $this->newLine();
            $this->error('Credenziali non OK. Su Plesk: carica il JSON del service account e usa un percorso ASSOLUTO nel .env.');
            $this->line('Esempio: GOOGLE_APPLICATION_CREDENTIALS=/var/www/vhosts/tuodominio/httpdocs/storage/app/google-credentials.json');

            return self::FAILURE;
        }

        try {
            $client = new DocumentProcessorServiceClient([
                'credentials' => $diag['resolved'],
            ]);
            $name = $client->processorName(
                (string) $diag['project_id'],
                (string) $diag['location'],
                (string) $diag['processor_id'],
            );
            $request = new GetProcessorRequest;
            $request->setName($name);
            $client->getProcessor($request);
            $client->close();
            $this->info('Connessione a Document AI OK (processor raggiungibile).');
        } catch (\Throwable $e) {
            $this->error('File credenziali trovato, ma API fallita: '.$e->getMessage());
            $this->line('Controlla: API Document AI abilitata, ruolo sul service account, processor ID e regione eu.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
