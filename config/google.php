<?php

return [

    'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),

    'document_ai' => [
        'location' => env('GOOGLE_DOCUMENT_AI_LOCATION', 'eu'),
        'processor_id' => env('GOOGLE_DOCUMENT_AI_PROCESSOR_ID'),
    ],

    /*
    | Percorso al JSON del service account Google Cloud.
    | Relativo alla root del progetto (es. storage/app/google-credentials.json)
    | oppure assoluto (obbligatorio consigliato su Plesk).
    */
    'application_credentials' => env('GOOGLE_APPLICATION_CREDENTIALS'),

];
