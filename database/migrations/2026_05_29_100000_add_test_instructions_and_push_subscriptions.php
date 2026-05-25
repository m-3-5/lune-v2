<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('development_items', function (Blueprint $table) {
            $table->text('test_instructions')->nullable()->after('body');
        });

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 16);
            $table->string('endpoint', 512)->unique();
            $table->string('public_key', 255)->nullable();
            $table->string('auth_token', 255)->nullable();
            $table->string('content_encoding', 32)->nullable();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['channel', 'reservation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');

        Schema::table('development_items', function (Blueprint $table) {
            $table->dropColumn('test_instructions');
        });
    }
};
