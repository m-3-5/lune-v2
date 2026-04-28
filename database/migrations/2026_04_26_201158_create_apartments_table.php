<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // <-- La colonna che prima mancava!
            $table->string('address')->nullable(); 
            $table->text('pre_booking_info')->nullable(); 
            $table->text('house_rules')->nullable(); 
            $table->text('stay_info')->nullable(); 
            $table->text('checkout_info')->nullable(); 
            $table->string('checkin_video_url')->nullable(); 
            $table->string('whatsapp_number')->nullable(); 
            $table->time('default_checkin_hour')->default('16:00'); 
            $table->string('access_code')->nullable(); 
            $table->string('checkfront_name')->nullable(); // <-- Il ponte
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartments');
    }
};