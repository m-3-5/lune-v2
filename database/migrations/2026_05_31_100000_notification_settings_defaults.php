<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $defaults = [
            'admin_notifications_enabled' => false,
            'admin_email_notifications_enabled' => false,
            'admin_whatsapp_notifications_enabled' => false,
            'guest_notifications_enabled' => false,
            'guest_email_notifications_enabled' => false,
            'guest_whatsapp_notifications_enabled' => false,
            'guest_push_notifications_enabled' => false,
            'admin_emails' => ['startupm3.5@gmail.com'],
            'admin_phones' => ['+393487564418'],
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
