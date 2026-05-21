<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'extracted_guests')) {
                $table->json('extracted_guests')->nullable()->after('checkfront_line_items');
            }
            if (! Schema::hasColumn('reservations', 'contract_locale')) {
                $table->string('contract_locale', 5)->default('it')->after('extracted_guests');
            }
            if (! Schema::hasColumn('reservations', 'contract_ready_for_guest')) {
                $table->boolean('contract_ready_for_guest')->default(false)->after('contract_locale');
            }
            if (! Schema::hasColumn('reservations', 'contract_extracted_at')) {
                $table->timestamp('contract_extracted_at')->nullable()->after('contract_ready_for_guest');
            }
            if (! Schema::hasColumn('reservations', 'checkfront_language')) {
                $table->string('checkfront_language', 20)->nullable()->after('contract_extracted_at');
            }
        });

        Schema::table('guest_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('guest_documents', 'guest_slot')) {
                $table->unsignedTinyInteger('guest_slot')->default(1)->after('reservation_id');
            }
            if (! Schema::hasColumn('guest_documents', 'is_foreigner')) {
                $table->boolean('is_foreigner')->default(false)->after('guest_slot');
            }
            if (! Schema::hasColumn('guest_documents', 'tax_code')) {
                $table->string('tax_code', 16)->nullable()->after('extracted_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'extracted_guests',
                'contract_locale',
                'contract_ready_for_guest',
                'contract_extracted_at',
                'checkfront_language',
            ]);
        });

        Schema::table('guest_documents', function (Blueprint $table) {
            $table->dropColumn(['guest_slot', 'is_foreigner', 'tax_code']);
        });
    }
};
