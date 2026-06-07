<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaults = [
            'twilio_account_sid' => '',
            'twilio_auth_token' => null,
            'twilio_whatsapp_from' => '+14155238886',
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
