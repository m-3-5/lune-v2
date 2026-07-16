<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entry_videos', function (Blueprint $table) {
            $table->string('category', 24)->default('ingresso')->after('apartment_id');
            $table->string('video_url')->nullable()->after('video_path');
        });
    }

    public function down(): void
    {
        Schema::table('entry_videos', function (Blueprint $table) {
            $table->dropColumn(['category', 'video_url']);
        });
    }
};
