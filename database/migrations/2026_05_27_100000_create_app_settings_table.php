<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value');
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            'admin_emails' => [
                'tuaemail@esempio.it — da aggiornare',
            ],
            'admin_phones' => [
                '+39 … — da aggiornare',
            ],
            'project_base_cost' => 3800,
            'project_cost_entries' => [
                [
                    'label' => 'Modifiche importanti (agenda, sync Checkfront, notifiche, contratto IT/EN)',
                    'amount' => 250,
                    'date' => '2026-05-23',
                ],
            ],
        ];

        foreach ($defaults as $key => $value) {
            DB::table('app_settings')->insert([
                'key' => $key,
                'value' => json_encode($value),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
