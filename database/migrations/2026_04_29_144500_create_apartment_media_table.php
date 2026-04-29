<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartment_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['gallery', 'entry_step']); // Per distinguere le foto belle dai tutorial di ingresso
            $table->string('file_path');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('order_column')->default(0); // Per ordinare le foto o gli step
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_media');
    }
};
