<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Checkfront API (solo server-side: mai esporre key/secret al browser)
    |--------------------------------------------------------------------------
    */
    'host' => env('CHECKFRONT_HOST', 'jlune.checkfront.com'),

    'api_key' => env('CHECKFRONT_API_KEY'),

    'api_secret' => env('CHECKFRONT_API_SECRET'),

    'payment_url' => env('CHECKFRONT_PAYMENT_URL', 'https://jlune.checkfront.com/reserve/payment/'),

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
