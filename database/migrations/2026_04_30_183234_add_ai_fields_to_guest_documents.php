<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_documents', function (Blueprint $table) {
            // Saltiamo document_type perché sappiamo che c'è già
            if (!Schema::hasColumn('guest_documents', 'expiry_date')) {
                $table->date('expiry_date')->nullable();
            }
            if (!Schema::hasColumn('guest_documents', 'ai_raw_response')) {
                $table->json('ai_raw_response')->nullable();
            }
            if (!Schema::hasColumn('guest_documents', 'extracted_name')) {
                $table->string('extracted_name')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('guest_documents', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'ai_raw_response', 'extracted_name']);
        });
    }
};