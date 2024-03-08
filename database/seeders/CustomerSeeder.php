<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $customer = \App\Models\Customer::factory()->create();
        // 
        $billAdr = new \App\Models\Address();
        $billAdr->fill([
            'contact_id' => $customer->id,
            'type' => \App\Models\Address::TYPE_BILLING,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'address_street_1' => fake()->streetAddress(),
            'address_street_2' => fake()->streetName(),
            'city' => fake()->city(),
            'state' => fake()->country(),
            'zip' => fake()->postcode(),
        ]);
        $billAdr->save();
    }
}
