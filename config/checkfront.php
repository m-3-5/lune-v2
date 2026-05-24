<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Checkfront API (solo server-side: mai esporre key/secret al browser)
    |--------------------------------------------------------------------------
    */
    'host' => env('CHECKFRONT_HOST', 'jlune.checkfront.com'),

    // Fuso per interpretare start_date / end_date dei webhook e API
    'timezone' => env('CHECKFRONT_TIMEZONE', 'Europe/Rome'),

    'api_key' => env('CHECKFRONT_API_KEY'),

    'api_secret' => env('CHECKFRONT_API_SECRET'),

    // URL pagamento cliente (senza /payment/ — quella path dà 404 su jlune)
    'payment_url' => env('CHECKFRONT_PAYMENT_URL', 'https://jlune.checkfront.com/reserve/'),

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
