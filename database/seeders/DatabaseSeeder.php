<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Services\SupabaseAuthService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function __construct(
        private readonly SupabaseAuthService $supabaseAuthService
    ) {}

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Delete existing demo users to refresh data
        User::query()->whereIn('email', ['demo1@finko.test', 'demo2@finko.test'])->forceDelete();

        $demoUsers = [
            [
                'name' => 'Demo Student One',
                'email' => 'demo1@finko.test',
                'password' => 'password123',
            ],
            [
                'name' => 'Demo Student Two',
                'email' => 'demo2@finko.test',
                'password' => 'password123',
            ],
        ];

        $users = collect($demoUsers)->map(function (array $data) {
            // Try to create in Supabase first
            try {
                $response = $this->supabaseAuthService->signUp($data['email'], $data['password']);
                $supabaseId = $response['user']['id'] ?? null;
            } catch (RuntimeException $e) {
                // If user already exists in Supabase, try to get the ID by signing in
                try {
                    $response = $this->supabaseAuthService->signInWithPassword($data['email'], $data['password']);
                    $supabaseId = $response['user']['id'] ?? null;
                } catch (RuntimeException $ex) {
                    echo "Warning: Could not create or authenticate {$data['email']} in Supabase: {$ex->getMessage()}\n";
                    $supabaseId = null;
                }
            }

            // Create local user record
            return User::create([
                'supabase_user_id' => $supabaseId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
        });

        $users->each(fn (User $user) => $this->seedFinanceDataForUser($user));
    }

    private function seedFinanceDataForUser(User $user): void
    {
        $categoryBlueprints = collect([
            ['name' => 'Food', 'type' => 'expense', 'color' => '#22c55e'],
            ['name' => 'Transportation', 'type' => 'expense', 'color' => '#0ea5e9'],
            ['name' => 'School', 'type' => 'expense', 'color' => '#6366f1'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Bills', 'type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Allowance', 'type' => 'income', 'color' => '#10b981'],
            ['name' => 'Part-time Job', 'type' => 'income', 'color' => '#14b8a6'],
            ['name' => 'Savings', 'type' => 'both', 'color' => '#84cc16'],
        ])->shuffle()->take(6)->values();

        $categories = $categoryBlueprints->map(function (array $categoryData) use ($user) {
            return Category::create([
                'user_id' => $user->id,
                'name' => $categoryData['name'],
                'type' => $categoryData['type'],
                'color' => $categoryData['color'],
                'description' => fake()->optional()->sentence(),
            ]);
        });

        $budgetCategories = $categories->whereIn('type', ['expense', 'both'])->values();
        if ($budgetCategories->isEmpty()) {
            $budgetCategories = $categories;
        }

        $budgets = collect();
        $budgetCount = 10;

        for ($index = 0; $index < $budgetCount; $index++) {
            $category = $budgetCategories->random();
            $periodStart = fake()->dateTimeBetween('-3 months', '-1 week');

            $budgets->push(Budget::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'title' => $category->name . ' Budget',
                'allocated_amount' => fake()->randomFloat(2, 800, 10000),
                'period_start' => $periodStart,
                'period_end' => (clone $periodStart)->modify('+30 days'),
                'status' => fake()->randomElement(['active', 'completed']),
                'notes' => fake()->optional()->sentence(),
            ]));
        }

        $transactions = collect();
        foreach ($budgets as $budget) {
            $transactionCount = fake()->numberBetween(1, 2);
            for ($i = 0; $i < $transactionCount; $i++) {
                $transactionType = fake()->randomElement(['expense', 'expense', 'income']);
                $category = $this->pickCategoryForType($categories, $transactionType, $budget->category_id);
                $transactions->push($this->createTransactionForUser($user, $budget, $category, $transactionType));
            }
        }

        $targetTransactionCount = 25;
        $remaining = max(0, $targetTransactionCount - $transactions->count());

        for ($index = 0; $index < $remaining; $index++) {
            $transactionType = fake()->randomElement(['expense', 'expense', 'expense', 'income']);
            $category = $this->pickCategoryForType($categories, $transactionType);
            $budget = $budgets->random();
            $transactions->push($this->createTransactionForUser($user, $budget, $category, $transactionType));
        }
    }

    private function pickCategoryForType($categories, string $type, ?int $preferredCategoryId = null): Category
    {
        if ($preferredCategoryId) {
            $preferred = $categories->firstWhere('id', $preferredCategoryId);
            if ($preferred && in_array($preferred->type, [$type, 'both'], true)) {
                return $preferred;
            }
        }

        $matched = $categories->filter(fn (Category $category) => in_array($category->type, [$type, 'both'], true));
        if ($matched->isEmpty()) {
            return $categories->random();
        }

        return $matched->random();
    }

    private function createTransactionForUser(User $user, Budget $budget, Category $category, string $type): Transaction
    {
        $expenseTitles = [
            'Lunch at Campus',
            'Jeepney Fare',
            'School Supplies',
            'Printing Costs',
            'Project Materials',
            'Internet Subscription',
            'Utility Payment',
        ];
        $incomeTitles = [
            'Weekly Allowance',
            'Part-time Shift Pay',
            'Scholarship Stipend',
            'Freelance Payment',
            'Cash Gift',
        ];

        return Transaction::create([
            'user_id' => $user->id,
            'budget_id' => $budget->id,
            'category_id' => $category->id,
            'type' => $type,
            'title' => fake()->randomElement($type === 'income' ? $incomeTitles : $expenseTitles),
            'amount' => $type === 'income'
                ? fake()->randomFloat(2, 400, 6000)
                : fake()->randomFloat(2, 50, 2500),
            'transaction_date' => fake()->dateTimeBetween('-90 days', 'now'),
            'payment_method' => fake()->randomElement(['Cash', 'GCash', 'Card', 'Bank Transfer']),
            'notes' => fake()->optional()->sentence(),
            'attachment_path' => null,
        ]);
    }
}

