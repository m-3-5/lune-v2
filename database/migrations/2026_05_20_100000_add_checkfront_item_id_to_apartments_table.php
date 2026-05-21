<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            if (! Schema::hasColumn('apartments', 'checkfront_item_id')) {
                $table->string('checkfront_item_id', 20)->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('apartments', 'checkfront_category_id')) {
                $table->unsignedTinyInteger('checkfront_category_id')->nullable()->after('sku');
            }

            if (! Schema::hasColumn('apartments', 'display_order')) {
                $table->unsignedTinyInteger('display_order')->default(0)->after('checkfront_category_id');
            }
        });

        try {
            Schema::table('apartments', function (Blueprint $table) {
                $table->unique('sku');
            });
        } catch (\Throwable) {
            // Indice già presente
        }
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            if (Schema::hasColumn('apartments', 'checkfront_item_id')) {
                $table->dropUnique(['checkfront_item_id']);
                $table->dropColumn('checkfront_item_id');
            }
            if (Schema::hasColumn('apartments', 'checkfront_category_id')) {
                $table->dropColumn('checkfront_category_id');
            }
            if (Schema::hasColumn('apartments', 'display_order')) {
                $table->dropColumn('display_order');
            }
        });
    }
};
