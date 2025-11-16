<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_name' => fake()->name(),
            'due_date' => fake()->dateTimeBetween('now', '+60 days'),
            'status' => fake()->randomElement(['DRAFT', 'SUBMITTED', 'PAID']),
            'total_amount' => fake()->randomFloat(2, 100, 10000),
            'paid_amount' => 0,
        ];
    }
}


