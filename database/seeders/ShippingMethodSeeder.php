<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShippingMethod;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ShippingMethod::updateOrCreate(
            ['code' => 'bpost'],
            ['name' => 'bpost (2–4 jours)', 'price' => 5.90, 'free_from' => 60.00]
        );

        ShippingMethod::updateOrCreate(
            ['code' => 'pickup'],
            ['name' => 'Retrait chez la créatrice', 'price' => 0.00, 'free_from' => null]
        );
    }
}

