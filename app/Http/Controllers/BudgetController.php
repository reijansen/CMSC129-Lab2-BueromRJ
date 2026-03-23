<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->attributes->get('app_user_id');
        $search = (string) $request->query('q', '');
        $categoryId = (int) $request->query('category_id', 0);

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when($categoryId > 0, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->with('category')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return view('budgets.index', compact('budgets', 'categories', 'search', 'categoryId'));
    }

    public function create(Request $request): View
    {
        $userId = (int) $request->attributes->get('app_user_id');
        $categories = Category::query()->where('user_id', $userId)->orderBy('name')->get();

        return view('budgets.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = (int) $request->attributes->get('app_user_id');
        $payload = $this->validateBudget($request);

        Budget::create([
            ...$payload,
            'user_id' => $userId,
        ]);

        return redirect()->route('budgets.index')->with('status', 'Budget created.');
    }

    public function edit(Request $request, int $budget): View
    {
        $userId = (int) $request->attributes->get('app_user_id');
        $budget = Budget::query()->where('user_id', $userId)->findOrFail($budget);
        $categories = Category::query()->where('user_id', $userId)->orderBy('name')->get();

        return view('budgets.edit', compact('budget', 'categories'));
    }

    public function update(Request $request, int $budget): RedirectResponse
    {
        $userId = (int) $request->attributes->get('app_user_id');
        $budget = Budget::query()->where('user_id', $userId)->findOrFail($budget);
        $payload = $this->validateBudget($request);
        $budget->update($payload);

        return redirect()->route('budgets.index')->with('status', 'Budget updated.');
    }

    public function destroy(Request $request, int $budget): RedirectResponse
    {
        $userId = (int) $request->attributes->get('app_user_id');
        $budget = Budget::query()->where('user_id', $userId)->findOrFail($budget);
        $budget->delete();

        return redirect()->route('budgets.index')->with('status', 'Budget deleted.');
    }

    private function validateBudget(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'amount_limit' => ['required', 'numeric', 'min:0'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date', 'after_or_equal:period_start'],
        ]);
    }
}

