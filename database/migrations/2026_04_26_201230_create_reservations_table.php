<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            // ECCO LA COLONNA CHE MANCAVA! Collega la prenotazione all'appartamento:
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade'); 
            
            $table->string('guest_name');
            $table->string('guest_email')->nullable();
            $table->string('token')->unique(); 
            $table->dateTime('check_in'); 
            $table->dateTime('check_out'); 
            
            $table->boolean('is_paid')->default(false); 
            $table->boolean('documents_validated')->default(false); 
            $table->integer('adults')->default(1);
            $table->integer('children')->default(0);
            
            $table->integer('internal_rating')->nullable(); 
            $table->text('internal_comment')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
