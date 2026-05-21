<?php

namespace Database\Seeders;

use App\Models\Apartment;
use Illuminate\Database\Seeder;

class ApartmentSeeder extends Seeder
{
    /**
     * Allineato all'inventario Checkfront (screenshot + log webhook maggio 2026).
     * item_id di Casa Tiziana: valorizzato da `php artisan checkfront:sync-items`.
     */
    public function run(): void
    {
        $apartments = [
            [
                'checkfront_item_id' => '13',
                'sku' => 'tuora',
                'name' => '"Tùora" Monolocale standard',
                'checkfront_category_id' => 3,
                'display_order' => 7,
            ],
            [
                'checkfront_item_id' => '6',
                'sku' => 'chessa',
                'name' => '"Chessa \'e Monte" Monolocale c...',
                'checkfront_category_id' => 3,
                'display_order' => 6,
            ],
            [
                'checkfront_item_id' => '5',
                'sku' => 'armidda',
                'name' => '"Armidda" Monolocale vista mare',
                'checkfront_category_id' => 3,
                'display_order' => 5,
            ],
            [
                'checkfront_item_id' => '10',
                'sku' => 'nemula',
                'name' => '"Nèmula" Monolocale vista mare',
                'checkfront_category_id' => 3,
                'display_order' => 4,
            ],
            [
                'checkfront_item_id' => '4',
                'sku' => 'kalavriche',
                'name' => '"Kalàvriche" Bilocale vista mare',
                'checkfront_category_id' => 2,
                'display_order' => 3,
            ],
            [
                'checkfront_item_id' => '9',
                'sku' => 'iscra',
                'name' => '"Iscrària" Bilocale con balcone',
                'checkfront_category_id' => 2,
                'display_order' => 2,
            ],
            [
                'checkfront_item_id' => null,
                'sku' => 'bilocalevist_copy',
                'name' => 'Casa Tiziana',
                'checkfront_category_id' => null,
                'display_order' => 1,
            ],
            [
                'checkfront_item_id' => '8',
                'sku' => 'tiria',
                'name' => '"Tìria" Trilocale',
                'checkfront_category_id' => 4,
                'display_order' => 1,
            ],
        ];

        foreach ($apartments as $apt) {
            Apartment::updateOrCreate(['sku' => $apt['sku']], $apt);
        }
    }
}
