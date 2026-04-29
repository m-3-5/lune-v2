<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Aggiungiamo la spunta del contratto alle Prenotazioni
        Schema::table('reservations', function (Blueprint $table) {
            $table->boolean('contract_accepted')->default(false)->after('documents_validated');
            $table->timestamp('contract_accepted_at')->nullable()->after('contract_accepted');
        });

        // 2. Creiamo da zero la tabella per i documenti con TUTTI i campi Polizia (Alloggiati Web)
        Schema::create('guest_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade'); 
            
            $table->string('full_name')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            
            $table->string('document_type')->nullable(); 
            $table->string('document_number')->nullable();
            $table->string('document_issue_place')->nullable();
            $table->string('file_path'); 
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['contract_accepted', 'contract_accepted_at']);
        });

        Schema::dropIfExists('guest_documents');
    }
};
