<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('telegram_chat_id', 32)->nullable()->after('guest_phone');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_chat_id');
        });

        if (Schema::hasTable('app_settings')) {
            \App\Models\AppSetting::query()->updateOrCreate(
                ['key' => 'guest_telegram_notifications_enabled'],
                ['value' => false],
            );
        }
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_linked_at']);
        });
    }
};
