<?php

return [

    'bot_token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    | Chat ID numerici (uno per riga in .env separati da virgola).
    | Ottienili avviando il bot e visitando /admin/sviluppo → link «Verifica Telegram».
    */
    'notify_chat_ids' => array_filter(array_map(
        'trim',
        explode(',', (string) env('TELEGRAM_NOTIFY_CHAT_IDS', ''))
    )),

    'enabled' => env('TELEGRAM_ENABLED', false),

];
