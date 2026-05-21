<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (! Schema::hasColumn('reservations', 'checkfront_status')) {
                $table->string('checkfront_status', 20)->nullable()->after('status');
            }
            if (! Schema::hasColumn('reservations', 'paid_total')) {
                $table->decimal('paid_total', 10, 2)->nullable()->after('total_price');
            }
            if (! Schema::hasColumn('reservations', 'balance')) {
                $table->decimal('balance', 10, 2)->nullable()->after('paid_total');
            }
            if (! Schema::hasColumn('reservations', 'checkfront_item_id')) {
                $table->string('checkfront_item_id', 20)->nullable()->after('checkfront_booking_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('reservations', 'checkfront_status') ? 'checkfront_status' : null,
                Schema::hasColumn('reservations', 'paid_total') ? 'paid_total' : null,
                Schema::hasColumn('reservations', 'balance') ? 'balance' : null,
                Schema::hasColumn('reservations', 'checkfront_item_id') ? 'checkfront_item_id' : null,
            ]));
        });
    }
};
