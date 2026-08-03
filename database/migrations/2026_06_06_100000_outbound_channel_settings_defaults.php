<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaults = [
            'mail_smtp_enabled' => false,
            'mail_smtp_host' => 'out.postassl.it',
            'mail_smtp_port' => 465,
            'mail_smtp_encryption' => 'ssl',
            'mail_smtp_username' => 'appjlune@inm35.net',
            'mail_smtp_password' => null,
            'mail_from_address' => 'appjlune@inm35.net',
            'mail_from_name' => 'Gestione Appartamenti',
            'whatsapp_provider' => 'log',
            'whatsapp_callmebot_keys' => [],
        ];

        foreach ($defaults as $key => $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => json_encode($value), 'updated_at' => $now, 'created_at' => $now]
            );
        }
    }

    public function down(): void
    {
        // non distruttivo
    }
};
