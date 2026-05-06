<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Apartment;

class ApartmentSeeder extends Seeder
{
    public function run(): void
    {
        $apartments = [
            ['name' => '"Tùora" Monolocale standard', 'sku' => 'tuora'],
            ['name' => '"Chessa \'e Monte" Monolocale c...', 'sku' => 'chessa'],
            ['name' => '"Armidda" Monolocale vista mare', 'sku' => 'armidda'],
            ['name' => '"Nèmula" Monolocale vista mare', 'sku' => 'nemula'],
            ['name' => '"Kalàvriche" Bilocale vista mare', 'sku' => 'kalavriche'],
            ['name' => '"Iscrarìa" Bilocale con balcone', 'sku' => 'iscra'],
            ['name' => 'Casa Tiziana', 'sku' => 'bilocalevist_copy'],
            ['name' => '"Tirìa" Trilocale', 'sku' => 'tiria']
        ];

        foreach ($apartments as $apt) {
            // updateOrCreate evita di creare doppioni se lanci il comando due volte
            Apartment::updateOrCreate(
                ['sku' => $apt['sku']], // Cerca per SKU
                ['name' => $apt['name']] // Se lo trova aggiorna il nome, altrimenti crea nuovo
            );
        }
    }
}