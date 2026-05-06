<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Aggiungiamo lo SKU agli appartamenti
        Schema::table('apartments', function (Blueprint $table) {
            if (!Schema::hasColumn('apartments', 'sku')) {
                $table->string('sku', 50)->nullable()->after('name');
            }
        });

        // 2. Aggiungiamo i dati economici alle prenotazioni
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'total_price')) {
                $table->decimal('total_price', 10, 2)->nullable()->after('is_paid');
            }
            if (!Schema::hasColumn('reservations', 'booking_code')) {
                $table->string('booking_code', 50)->nullable()->after('checkfront_booking_id');
            }
            if (!Schema::hasColumn('reservations', 'status')) {
                $table->string('status', 20)->nullable()->after('total_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropColumn('sku');
        });
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['total_price', 'booking_code', 'status']);
        });
    }
};
