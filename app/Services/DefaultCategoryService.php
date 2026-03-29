<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class DefaultCategoryService
{
    /**
     * @var array<int, array{name: string, type: string, description: ?string}>
     */
    private const DEFAULT_CATEGORIES = [
        ['name' => 'Allowance', 'type' => 'income', 'description' => null],
        ['name' => 'Books & Supplies', 'type' => 'expense', 'description' => null],
        ['name' => 'Debt / Loans', 'type' => 'expense', 'description' => null],
        ['name' => 'Entertainment', 'type' => 'expense', 'description' => null],
        ['name' => 'Food & Drinking', 'type' => 'expense', 'description' => null],
        ['name' => 'Groceries', 'type' => 'expense', 'description' => null],
        ['name' => 'Health & Medicine', 'type' => 'expense', 'description' => null],
        ['name' => 'Miscellaneous', 'type' => 'expense', 'description' => null],
        ['name' => 'Personal Care', 'type' => 'expense', 'description' => null],
        ['name' => 'Rent / Housing', 'type' => 'expense', 'description' => null],
        ['name' => 'Savings', 'type' => 'expense', 'description' => null],
        ['name' => 'School / Tuition', 'type' => 'expense', 'description' => null],
        ['name' => 'Shopping / Clothes', 'type' => 'expense', 'description' => null],
        ['name' => 'Transportation', 'type' => 'expense', 'description' => null],
        ['name' => 'Utilities', 'type' => 'expense', 'description' => 'electricity, water, internet, mobile load'],
    ];

    public function seedIfEmpty(User $user): void
    {
        if ($user->categories()->exists()) {
            return;
        }

        foreach (self::DEFAULT_CATEGORIES as $defaultCategory) {
            Category::create([
                'user_id' => $user->id,
                'name' => $defaultCategory['name'],
                'type' => $defaultCategory['type'],
                'color' => null,
                'description' => $defaultCategory['description'],
            ]);
        }
    }
}

