<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Aggiungiamo l'ID di Checkfront (es. BVYN-220426)
            $table->string('checkfront_booking_id')->nullable()->after('id');
            
            // Aggiungiamo il link generato da Checkfront per far pagare l'acconto
            $table->text('checkfront_payment_url')->nullable()->after('token');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['checkfront_booking_id', 'checkfront_payment_url']);
        });
    }
};
