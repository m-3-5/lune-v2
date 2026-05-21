<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'documents_submitted_at')) {
                $table->timestamp('documents_submitted_at')->nullable()->after('documents_validated');
            }
            if (! Schema::hasColumn('reservations', 'checkfront_line_items')) {
                $table->json('checkfront_line_items')->nullable()->after('checkfront_item_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['documents_submitted_at', 'checkfront_line_items']);
        });
    }
};
