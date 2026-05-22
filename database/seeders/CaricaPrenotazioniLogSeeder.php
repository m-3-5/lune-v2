<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reservation;
use Carbon\Carbon;

class CaricaPrenotazioniLogSeeder extends Seeder
{
    public function run(): void
    {
        // Rimuove eventuali inserimenti parziali precedenti
        Reservation::truncate();

        // 1. Prenotazione Juan Carlos Van Sikle - Tìria (ID Appartamento su database: 8)
        Reservation::create([
            'booking_id' => 'LLNK-200526',
            'apartment_id' => 8, 
            'customer_name' => 'Juan Carlos Van Sikle',
            'customer_email' => 'vansikle@example.com',
            'customer_phone' => '123456789',
            'checkin' => '2026-05-22',
            'checkout' => '2026-05-25',
            'status' => 'WAIT',
            'amount' => 720.00,
            'raw_data' => json_encode(['note' => 'Caricato da log storici']),
        ]);

        // 2. Prenotazione Juan Carlos Van Sikle - Armidda (ID Appartamento su database: 3)
        Reservation::create([
            'booking_id' => 'LLNK-200526',
            'apartment_id' => 3, 
            'customer_name' => 'Juan Carlos Van Sikle',
            'customer_email' => 'vansikle@example.com',
            'customer_phone' => '123456789',
            'checkin' => '2026-05-22',
            'checkout' => '2026-05-25',
            'status' => 'WAIT',
            'amount' => 0.00,
            'raw_data' => json_encode(['note' => 'Caricato da log storici']),
        ]);

        // 3. Prenotazione Elena Rossi - Chessa \'e Monte (ID Appartamento su database: 2)
        Reservation::create([
            'booking_id' => 'MKTY-180526',
            'apartment_id' => 2, 
            'customer_name' => 'Elena Rossi',
            'customer_email' => 'elena@example.com',
            'customer_phone' => '987654321',
            'checkin' => '2026-06-10',
            'checkout' => '2026-06-17',
            'status' => 'PAID',
            'amount' => 500.00,
            'raw_data' => json_encode(['note' => 'Caricato da log storici']),
        ]);
    }
}
