<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $supplier = \App\Models\Supplier::factory()->create();
        // 
        $billAdr = new \App\Models\Address();
        $billAdr->fill([
            'contact_id' => $supplier->id,
            'type' => \App\Models\Address::TYPE_BILLING,
            'name' => $supplier->name,
            'phone' => $supplier->phone,
            'address_street_1' => fake()->streetAddress(),
            'address_street_2' => fake()->streetName(),
            'city' => fake()->city(),
            'state' => fake()->country(),
            'zip' => fake()->postcode(),
        ]);
        $billAdr->save();
    }
}
