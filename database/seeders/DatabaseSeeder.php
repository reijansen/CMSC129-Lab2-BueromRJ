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
            ['name' => 'Groceries', 'type' => 'expense', 'color' => '#16a34a'],
            ['name' => 'Dorm/Boarding Rent', 'type' => 'expense', 'color' => '#0f766e'],
            ['name' => 'Utilities (Electric/Water)', 'type' => 'expense', 'color' => '#0ea5e9'],
            ['name' => 'Internet/Data Load', 'type' => 'expense', 'color' => '#2563eb'],
            ['name' => 'Laundry', 'type' => 'expense', 'color' => '#a855f7'],
            ['name' => 'Toiletries & Essentials', 'type' => 'expense', 'color' => '#db2777'],
            ['name' => 'Transportation', 'type' => 'expense', 'color' => '#0ea5e9'],
            ['name' => 'School', 'type' => 'expense', 'color' => '#6366f1'],
            ['name' => 'Printing & Photocopy', 'type' => 'expense', 'color' => '#475569'],
            ['name' => 'School Supplies', 'type' => 'expense', 'color' => '#334155'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Bills', 'type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Allowance', 'type' => 'income', 'color' => '#10b981'],
            ['name' => 'Scholarship', 'type' => 'income', 'color' => '#14b8a6'],
            ['name' => 'Part-time Job', 'type' => 'income', 'color' => '#14b8a6'],
            ['name' => 'Side Hustle/Freelance', 'type' => 'income', 'color' => '#06b6d4'],
            ['name' => 'Savings', 'type' => 'both', 'color' => '#84cc16'],
            ['name' => 'Emergency Fund', 'type' => 'both', 'color' => '#eab308'],
            ['name' => 'Health/Medicine', 'type' => 'expense', 'color' => '#f43f5e'],
            ['name' => 'Home Visits/Trips', 'type' => 'expense', 'color' => '#fb7185'],
        ])->shuffle()->take(10)->values();

        $categories = $categoryBlueprints->map(function (array $categoryData) use ($user) {
            return Category::create([
                'user_id' => $user->id,
                'name' => $categoryData['name'],
                'type' => $categoryData['type'],
                'color' => $categoryData['color'],
                'description' => fake()->optional()->sentence(),
            ]);
        });

        $budgetCategories = $categories->values();

        $budgets = collect();
        $budgetCount = 10;

        for ($index = 0; $index < $budgetCount; $index++) {
            $category = $budgetCategories->random();
            $monthAnchor = \Carbon\Carbon::instance(fake()->dateTimeBetween('-3 months', 'now'));
            $periodStart = $monthAnchor->copy()->startOfMonth();
            $periodEnd = $monthAnchor->copy()->endOfMonth();
            $allocatedAmount = $category->type === 'income'
                ? fake()->randomFloat(2, 1500, 12000)
                : fake()->randomFloat(2, 800, 10000);

            $budgets->push(Budget::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'title' => $category->name . ' Budget',
                'allocated_amount' => $allocatedAmount,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => fake()->randomElement(['active', 'completed']),
                'notes' => fake()->optional()->sentence(),
            ]));
        }

        $transactions = collect();
        foreach ($budgets as $budget) {
            $budgetCategory = $categories->firstWhere('id', $budget->category_id) ?? $categories->random();
            $transactionType = $budgetCategory->type === 'income' ? 'income' : 'expense';
            $transactionCount = fake()->numberBetween(2, 3);

            for ($i = 0; $i < $transactionCount; $i++) {
                $category = $this->pickCategoryForType($categories, $transactionType, $budget->category_id);
                $transactions->push($this->createTransactionForUser(
                    $user,
                    $budget,
                    $category,
                    $transactionType,
                    \Carbon\Carbon::parse($budget->period_start)->startOfDay(),
                    \Carbon\Carbon::parse($budget->period_end)->endOfDay()
                ));
            }
        }

        $targetTransactionCount = 25;
        $remaining = max(0, $targetTransactionCount - $transactions->count());

        for ($index = 0; $index < $remaining; $index++) {
            $transactionType = fake()->randomElement(['expense', 'expense', 'expense', 'income']);
            $category = $this->pickCategoryForType($categories, $transactionType);

            $budget = $this->pickBudgetForCategory($budgets, $categories, $category, $transactionType) ?? $budgets->random();

            $transactions->push($this->createTransactionForUser(
                $user,
                $budget,
                $category,
                $transactionType,
                \Carbon\Carbon::instance(fake()->dateTimeBetween('-90 days', 'now'))->startOfDay(),
                \Carbon\Carbon::now()->endOfDay()
            ));
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

    private function createTransactionForUser(
        User $user,
        Budget $budget,
        Category $category,
        string $type,
        \Carbon\Carbon $dateFrom,
        \Carbon\Carbon $dateTo
    ): Transaction
    {
        $expenseTitles = $this->expenseTitlesForCategory($category);
        $incomeTitles = $this->incomeTitlesForCategory($category);

        $amount = $this->amountForCategory($category, $type);

        return Transaction::create([
            'user_id' => $user->id,
            'budget_id' => $budget->id,
            'category_id' => $category->id,
            'type' => $type,
            'title' => fake()->randomElement($type === 'income' ? $incomeTitles : $expenseTitles),
            'amount' => $amount,
            'transaction_date' => fake()->dateTimeBetween($dateFrom, $dateTo),
            'payment_method' => fake()->randomElement(['Cash', 'GCash', 'Card', 'Bank Transfer']),
            'notes' => fake()->optional()->sentence(),
            'attachment_path' => null,
        ]);
    }

    private function pickBudgetForCategory($budgets, $categories, Category $category, string $transactionType): ?Budget
    {
        $matching = $budgets->where('category_id', $category->id)->values();
        if ($matching->isNotEmpty()) {
            return $matching->random();
        }

        $wantedCategoryTypes = $transactionType === 'income' ? ['income', 'both'] : ['expense', 'both'];

        $fallback = $budgets->filter(function (Budget $budget) use ($categories, $wantedCategoryTypes) {
            $type = $categories->firstWhere('id', $budget->category_id)?->type;

            return in_array($type, $wantedCategoryTypes, true);
        })->values();

        return $fallback->isNotEmpty() ? $fallback->random() : null;
    }

    private function expenseTitlesForCategory(Category $category): array
    {
        $name = strtolower($category->name);

        if (str_contains($name, 'food')) {
            return ['Lunch at Campus', 'Dinner at Carinderia', 'Snacks', 'Meal Prep Ingredients'];
        }

        if (str_contains($name, 'grocer')) {
            return ['Grocery Run', 'Market Vegetables', 'Rice & Essentials', 'Drinking Water'];
        }

        if (str_contains($name, 'rent') || str_contains($name, 'dorm') || str_contains($name, 'boarding')) {
            return ['Monthly Rent Payment', 'Advance Rent', 'Security Deposit'];
        }

        if (str_contains($name, 'utilit') || str_contains($name, 'electric') || str_contains($name, 'water')) {
            return ['Electric Bill', 'Water Bill', 'Utility Contribution'];
        }

        if (str_contains($name, 'internet') || str_contains($name, 'data') || str_contains($name, 'load')) {
            return ['Mobile Data Load', 'WiFi Share Payment', 'Internet Subscription'];
        }

        if (str_contains($name, 'laundr')) {
            return ['Laundry Service', 'Detergent & Soap', 'Drying Fee'];
        }

        if (str_contains($name, 'toiletr') || str_contains($name, 'essential')) {
            return ['Shampoo & Soap', 'Toothpaste', 'Tissue & Essentials', 'Cleaning Supplies'];
        }

        if (str_contains($name, 'transport')) {
            return ['Jeepney Fare', 'Tricycle Fare', 'Bus Fare', 'Grab/Taxi'];
        }

        if (str_contains($name, 'print') || str_contains($name, 'photo')) {
            return ['Printing Costs', 'Photocopy', 'Document Binding', 'ID Photo'];
        }

        if (str_contains($name, 'suppl')) {
            return ['School Supplies', 'Notebooks', 'Pens & Markers'];
        }

        if (str_contains($name, 'school')) {
            return ['Project Materials', 'Lab Fee', 'Org Dues', 'School Contribution'];
        }

        if (str_contains($name, 'health') || str_contains($name, 'med')) {
            return ['Medicine Purchase', 'Clinic Visit', 'Vitamins'];
        }

        if (str_contains($name, 'trip') || str_contains($name, 'visit')) {
            return ['Home Visit Fare', 'Weekend Trip', 'Terminal Fees'];
        }

        if (str_contains($name, 'entertain')) {
            return ['Movie Night', 'Cafe Treat', 'Gaming Load', 'Streaming Subscription'];
        }

        if (str_contains($name, 'bill')) {
            return ['General Bills Payment', 'Subscription Fee', 'Service Fee'];
        }

        return ['Misc Expense', 'Small Purchase', 'Unexpected Expense'];
    }

    private function incomeTitlesForCategory(Category $category): array
    {
        $name = strtolower($category->name);

        if (str_contains($name, 'allowance')) {
            return ['Weekly Allowance', 'Monthly Allowance', 'Allowance Top-up'];
        }

        if (str_contains($name, 'scholar')) {
            return ['Scholarship Stipend', 'Scholarship Allowance'];
        }

        if (str_contains($name, 'part-time')) {
            return ['Part-time Shift Pay', 'Overtime Pay'];
        }

        if (str_contains($name, 'freelance') || str_contains($name, 'side hustle')) {
            return ['Freelance Payment', 'Side Gig Payment'];
        }

        return ['Income Received', 'Cash Gift'];
    }

    private function amountForCategory(Category $category, string $type): float
    {
        $name = strtolower($category->name);

        if ($type === 'income') {
            if (str_contains($name, 'scholar')) {
                return fake()->randomFloat(2, 3000, 12000);
            }

            if (str_contains($name, 'allowance')) {
                return fake()->randomFloat(2, 800, 6000);
            }

            if (str_contains($name, 'part-time')) {
                return fake()->randomFloat(2, 1200, 8000);
            }

            return fake()->randomFloat(2, 500, 7000);
        }

        if (str_contains($name, 'rent') || str_contains($name, 'dorm') || str_contains($name, 'boarding')) {
            return fake()->randomFloat(2, 1500, 7000);
        }

        if (str_contains($name, 'utilit') || str_contains($name, 'electric') || str_contains($name, 'water')) {
            return fake()->randomFloat(2, 100, 2000);
        }

        if (str_contains($name, 'internet') || str_contains($name, 'data') || str_contains($name, 'load')) {
            return fake()->randomFloat(2, 50, 1500);
        }

        if (str_contains($name, 'food') || str_contains($name, 'grocer')) {
            return fake()->randomFloat(2, 50, 800);
        }

        if (str_contains($name, 'transport')) {
            return fake()->randomFloat(2, 20, 300);
        }

        if (str_contains($name, 'print') || str_contains($name, 'photo') || str_contains($name, 'suppl')) {
            return fake()->randomFloat(2, 20, 1200);
        }

        if (str_contains($name, 'health') || str_contains($name, 'med')) {
            return fake()->randomFloat(2, 50, 2500);
        }

        return fake()->randomFloat(2, 50, 2500);
    }
}
