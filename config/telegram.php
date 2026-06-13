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

    'bot_username' => env('TELEGRAM_BOT_USERNAME', 'jlune_notifiche_bot'),

    /*
    | Segreto opzionale per l'URL webhook (consigliato in produzione).
    | Imposta il webhook con: https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://TUODOMINIO/webhook/telegram/<SECRET>
    */
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),

];
