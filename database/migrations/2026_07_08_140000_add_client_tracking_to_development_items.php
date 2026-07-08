<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('development_items', function (Blueprint $table) {
            $table->string('client_name')->nullable()->after('author');
            $table->string('client_email')->nullable()->after('client_name');
            $table->string('public_token', 40)->nullable()->unique()->after('client_email');
        });
    }

    public function down(): void
    {
        Schema::table('development_items', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'client_email', 'public_token']);
        });
    }
};
