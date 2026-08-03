<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('development_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->string('status', 24)->default('open');
            $table->string('title');
            $table->text('body')->nullable();
            $table->string('author', 24)->default('client');
            $table->timestamps();

            $table->index(['type', 'status']);
        });

        Schema::create('development_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('development_item_id')->constrained()->cascadeOnDelete();
            $table->string('author', 24);
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('development_replies');
        Schema::dropIfExists('development_items');
    }
};
