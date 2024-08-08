<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'type' => \App\Models\Contact::TYPE_CUSTOMER,
            'name' => fake()->name(),
            'email' => fake()->freeEmail(),
            'phone' => fake()->e164PhoneNumber(),
            'website' => fake()->domainName(),
        ];
    }
}
