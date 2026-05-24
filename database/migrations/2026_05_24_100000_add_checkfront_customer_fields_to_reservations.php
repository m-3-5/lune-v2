<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'guest_phone')) {
                $table->string('guest_phone', 50)->nullable()->after('guest_email');
            }
            if (! Schema::hasColumn('reservations', 'guest_cognome')) {
                $table->string('guest_cognome')->nullable()->after('guest_name');
            }
            if (! Schema::hasColumn('reservations', 'checkfront_customer_code')) {
                $table->string('checkfront_customer_code', 50)->nullable()->after('checkfront_booking_id');
            }
            if (! Schema::hasColumn('reservations', 'checkfront_fields')) {
                $table->json('checkfront_fields')->nullable()->after('checkfront_line_items');
            }
            if (! Schema::hasColumn('reservations', 'checkfront_taxes')) {
                $table->json('checkfront_taxes')->nullable()->after('checkfront_fields');
            }
            if (! Schema::hasColumn('reservations', 'sub_total')) {
                $table->decimal('sub_total', 10, 2)->nullable()->after('total_price');
            }
            if (! Schema::hasColumn('reservations', 'tax_total')) {
                $table->decimal('tax_total', 10, 2)->nullable()->after('sub_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'guest_phone',
                'guest_cognome',
                'checkfront_customer_code',
                'checkfront_fields',
                'checkfront_taxes',
                'sub_total',
                'tax_total',
            ]);
        });
    }
};
