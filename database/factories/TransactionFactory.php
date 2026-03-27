<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
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
            'budget_id' => function (array $attributes): int {
                $userId = (int) $attributes['user_id'];
                $categoryId = (int) ($attributes['category_id'] ?? 0);

                $matchingBudgetId = Budget::query()
                    ->where('user_id', $userId)
                    ->when($categoryId > 0, fn ($query) => $query->where('category_id', $categoryId))
                    ->inRandomOrder()
                    ->value('id');

                if ($matchingBudgetId) {
                    return (int) $matchingBudgetId;
                }

                if ($categoryId > 0) {
                    return (int) BudgetFactory::new()->create([
                        'user_id' => $userId,
                        'category_id' => $categoryId,
                    ])->id;
                }

                return (int) BudgetFactory::new()->create([
                    'user_id' => $userId,
                ])->id;
            },
            'title' => fake()->randomElement([
                'Campus Lunch',
                'Jeepney Fare',
                'School Supplies',
                'Project Expense',
                'Allowance Received',
                'Online Subscription',
                'Savings Deposit',
            ]),
            'amount' => fake()->randomFloat(2, 50, 4000),
            'type' => fake()->randomElement(['income', 'expense']),
            'transaction_date' => fake()->dateTimeBetween('-60 days', 'now'),
            'payment_method' => fake()->randomElement(['Cash', 'GCash', 'Card', 'Bank Transfer']),
            'notes' => fake()->optional()->sentence(),
            'attachment_path' => null,
        ];
    }
}
