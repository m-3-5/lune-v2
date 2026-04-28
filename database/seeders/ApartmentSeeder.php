<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Apartment;

class ApartmentSeeder extends Seeder
{
    public function run(): void
    {
        $apartments = [
            ['name' => 'Némula', 'checkfront_name' => 'Monolocale Vista Mare Némula'],
            ['name' => 'Armidda', 'checkfront_name' => 'Monolocale Vista Mare Armidda'],
            ['name' => 'Iscrarìa', 'checkfront_name' => 'Appartamento Balcone Iscrarìa'],
            ['name' => 'Kalavriche', 'checkfront_name' => 'Appartamento Kalavriche 4-5pax'],
            ['name' => 'Tiria', 'checkfront_name' => 'Appartamento 2 Camere Da Letto'],
            ['name' => 'Tùora', 'checkfront_name' => '"Tùora" Monolocale standard'],
            ['name' => 'Zinnibiri', 'checkfront_name' => 'Monolocale Con Balcone'],
        ];

        foreach ($apartments as $apt) {
            Apartment::updateOrCreate(
                ['checkfront_name' => $apt['checkfront_name']], // Cerca se esiste già
                [
                    'name' => $apt['name'],
                    'address' => 'Lungomare Palmasera, 6, 08022 Cala Gonone' // <-- Aggiunto per accontentare il database!
                ]
            );
        }
    }
}