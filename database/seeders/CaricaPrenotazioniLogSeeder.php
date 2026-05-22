<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\CheckfrontBookingSync;

class CaricaPrenotazioniLogSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(CheckfrontBookingSync::class);
        
        // 1. Prenotazione Van Sikle (Tìria + Armidda)
        $service->syncFromWebhook([
            'booking' => [
                'id' => 'LLNK-200526',
                'status' => 'WAIT',
                'customer_id' => '12345',
                'customer_name' => 'Juan Carlos Van Sikle',
                'customer_email' => 'vansikle@example.com',
                'customer_phone' => '123456789',
                'start_date' => '2026-05-22',
                'end_date' => '2026-05-25',
                'items' => [
                    ['line_id' => '1', 'item_id' => '8', 'sku' => 'tiria', 'status' => 'WAIT', 'total' => '720.00'],
                    ['line_id' => '2', 'item_id' => '5', 'sku' => 'armidda', 'status' => 'WAIT', 'total' => '0.00']
                ]
            ]
        ]);

        // 2. Prenotazione Elena Rossi (Chessa 'e Monte)
        $service->syncFromWebhook([
            'booking' => [
                'id' => 'MKTY-180526',
                'status' => 'PAID',
                'customer_id' => '67890',
                'customer_name' => 'Elena Rossi',
                'customer_email' => 'elena@example.com',
                'customer_phone' => '987654321',
                'start_date' => '2026-06-10',
                'end_date' => '2026-06-17',
                'items' => [
                    ['line_id' => '1', 'item_id' => '6', 'sku' => 'chessa', 'status' => 'PAID', 'total' => '500.00']
                ]
            ]
        ]);
    }
}
