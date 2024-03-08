<?php

namespace Database\Factories;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\purchase>
 */
class PurchaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $refid = make_reference_id('PR', Purchase::max('id') + 1);
        $supplier_id = \App\Models\Contact::supplier()->get()->random()->id;
        $supplier = \App\Models\Contact::findOrFail($supplier_id);

        return [
            'reference' => $refid,
            'date' => fake()->dateTimeBetween('-2 months'),
            'supplier_id' => $supplier_id,
            'supplier_name' => $supplier->name,
            //
        ];
    }
}
