<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apartment_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained()->onDelete('cascade');
            $table->string('name'); // es: "Lavasciuga", "Macchina del Caffè"
            $table->text('instructions')->nullable(); // Testo
            $table->string('video_url')->nullable(); // Link YouTube
            $table->string('qr_code_image_path')->nullable(); // Il file del QR da stampare
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apartment_qr_codes');
    }
};
