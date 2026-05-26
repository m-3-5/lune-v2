<?php

return [

    /*
    | Password per area /admin/sviluppo (solo team di sviluppo).
    | Lasciare vuoto in locale per disabilitare il blocco (solo APP_ENV=local).
    */
    'dev_password' => env('JLUNE_DEV_PASSWORD', ''),

    /*
    | Prenotazioni di test manuali (area Sviluppo). Default: solo APP_ENV=local.
    | Su Plesk: JLUNE_TEST_BOOKINGS_ENABLED=true per abilitare temporaneamente.
    */
    'test_bookings_enabled' => env(
        'JLUNE_TEST_BOOKINGS_ENABLED',
        env('APP_ENV', 'production') === 'local'
    ),

];
