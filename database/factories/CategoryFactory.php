<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense', 'both']);
        $namePool = match ($type) {
            'income' => ['Allowance', 'Part-time Job', 'Scholarship', 'Freelance', 'Cash Gift', 'Refund'],
            'expense' => ['Food', 'Transportation', 'School', 'Entertainment', 'Bills', 'Health', 'Shopping'],
            default => ['Savings', 'Emergency Fund', 'Miscellaneous', 'Personal'],
        };

        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement($namePool) . ' ' . fake()->unique()->numberBetween(1, 999),
            'type' => $type,
            'color' => fake()->safeHexColor(),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
