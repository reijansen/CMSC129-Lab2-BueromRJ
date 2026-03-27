<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    public function definition(): array
    {
        $periodStart = fake()->dateTimeBetween('-3 months', 'now');
        $status = fake()->randomElement(['active', 'completed', 'exceeded', 'archived']);

        return [
            'user_id' => User::factory(),
            'category_id' => function (array $attributes): int {
                $userId = (int) $attributes['user_id'];

                $existingCategoryId = Category::query()
                    ->where('user_id', $userId)
                    ->inRandomOrder()
                    ->value('id');

                if ($existingCategoryId) {
                    return (int) $existingCategoryId;
                }

                return (int) CategoryFactory::new()->create([
                    'user_id' => $userId,
                ])->id;
            },
            'title' => fake()->randomElement([
                'Monthly Budget',
                'Essentials Plan',
                'Semester Spending',
                'Savings Target',
                'Allowance Plan',
            ]) . ' ' . fake()->monthName(),
            'allocated_amount' => fake()->randomFloat(2, 500, 12000),
            'period_start' => $periodStart,
            'period_end' => (clone $periodStart)->modify('+30 days'),
            'status' => $status,
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
