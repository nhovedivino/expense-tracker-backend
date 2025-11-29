<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Food', 'Transport', 'Entertainment', 'Healthcare', 'Shopping', 'Utilities', 'Education', 'Travel'];
        
        return [
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 5, 500),
            'description' => $this->faker->sentence(3),
            'category' => $this->faker->randomElement($categories),
            'date' => $this->faker->date(),
        ];
    }
}
