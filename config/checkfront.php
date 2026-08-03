<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Checkfront API (solo server-side: mai esporre key/secret al browser)
    |--------------------------------------------------------------------------
    */
    'host' => env('CHECKFRONT_HOST', ''),

    // Fuso per interpretare start_date / end_date dei webhook e API
    'timezone' => env('CHECKFRONT_TIMEZONE', 'Europe/Rome'),

    'api_key' => env('CHECKFRONT_API_KEY'),

    'api_secret' => env('CHECKFRONT_API_SECRET'),

    // URL pagamento cliente (senza /payment/ — quella path dà spesso 404)
    'payment_url' => env('CHECKFRONT_PAYMENT_URL', ''),

    /*
    | Log webhook (opzionale). Se vuoto, import-log usa il primo file esistente tra:
    | - app/checkfront_data/laravel.log (locale)
    | - storage/logs/laravel.log (Plesk, si aggiorna a ogni webhook)
    */
    'webhook_log_path' => env('CHECKFRONT_WEBHOOK_LOG_PATH'),

    /*
    | Etichette leggibili per SKU extra (oltre all'appartamento)
    */
    'extra_item_labels' => [
        'culla' => 'Culla',
        'tassadisoggiorno' => 'Tassa di soggiorno',
        'fraisbancaires' => 'Commissioni bancarie',
    ],

    /*
    | SKU da ignorare nei webhook (extra, tasse, servizi)
    */
    'excluded_item_skus' => [
        'culla',
        'tassadisoggiorno',
        'fraisbancaires',
    ],

    /*
    | category_id Checkfront degli alloggi (Monolocali, Bilocali, Trilocali)
    */
    'apartment_category_ids' => ['2', '3', '4'],

];
