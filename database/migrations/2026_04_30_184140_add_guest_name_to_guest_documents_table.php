<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_documents', function (Blueprint $table) {
            // Verifica se la colonna esiste già per evitare doppioni
            if (!Schema::hasColumn('guest_documents', 'guest_name')) {
                $table->string('guest_name')->nullable()->after('reservation_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guest_documents', function (Blueprint $table) {
            $table->dropColumn('guest_name');
        });
    }
};
