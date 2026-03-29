<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $this->currentUserId($request);
        $search = trim((string) $request->query('q', ''));
        $type = (string) $request->query('type', '');
        $tab = (string) $request->query('tab', 'active');
        $isDeletedTab = $tab === 'deleted';

        $categoriesQuery = Category::query()
            ->where('user_id', $userId)
            ->withCount([
                'budgets as budgets_count' => fn ($query) => $query->where('user_id', $userId)->withTrashed(),
                'transactions as transactions_count' => fn ($query) => $query->where('user_id', $userId)->withTrashed(),
            ]);

        if ($isDeletedTab) {
            $categoriesQuery->onlyTrashed();
        }

        if ($search !== '') {
            $categoriesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            });
        }

        if (in_array($type, ['income', 'expense', 'both'], true)) {
            $categoriesQuery->where('type', $type);
        }

        $categories = $categoriesQuery
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
            'search' => $search,
            'type' => $type,
            'tab' => $tab,
        ]);
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $this->currentUserId($request);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense,both'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        Category::create([
            ...$payload,
            'user_id' => $userId,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Request $request, Category $category): View
    {
        $this->authorizeCategoryOwnership($request, $category);
        $userId = $this->currentUserId($request);

        $relatedBudgets = $category->budgets()
            ->where('user_id', $userId)
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();

        $relatedTransactions = $category->transactions()
            ->where('user_id', $userId)
            ->with('budget')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('categories.show', [
            'category' => $category,
            'relatedBudgets' => $relatedBudgets,
            'relatedTransactions' => $relatedTransactions,
        ]);
    }

    public function edit(Request $request, Category $category): View
    {
        $this->authorizeCategoryOwnership($request, $category);

        return view('categories.edit', [
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategoryOwnership($request, $category);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense,both'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        $category->update($payload);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategoryOwnership($request, $category);

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category archived successfully.');
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $category = $this->trashedCategoryForUser($request, $id);
        $category->restore();

        return redirect()
            ->route('categories.index', ['tab' => 'deleted'])
            ->with('success', 'Category restored successfully.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $category = $this->trashedCategoryForUser($request, $id);

        $hasRelatedBudgets = Budget::withTrashed()
            ->where('user_id', $this->currentUserId($request))
            ->where('category_id', $category->id)
            ->exists();

        $hasRelatedTransactions = Transaction::withTrashed()
            ->where('user_id', $this->currentUserId($request))
            ->where('category_id', $category->id)
            ->exists();

        if ($hasRelatedBudgets || $hasRelatedTransactions) {
            return redirect()
                ->route('categories.index', ['tab' => 'deleted'])
                ->with('error', 'Category cannot be permanently deleted because it is referenced by budgets or transactions.');
        }

        try {
            $category->forceDelete();
        } catch (QueryException) {
            return redirect()
                ->route('categories.index', ['tab' => 'deleted'])
                ->with('error', 'Category cannot be permanently deleted due to a database constraint.');
        }

        return redirect()
            ->route('categories.index', ['tab' => 'deleted'])
            ->with('success', 'Category permanently deleted.');
    }

    private function authorizeCategoryOwnership(Request $request, Category $category): void
    {
        if ((int) $category->user_id !== $this->currentUserId($request)) {
            abort(403);
        }
    }

    private function trashedCategoryForUser(Request $request, int $id): Category
    {
        $category = Category::onlyTrashed()
            ->where('user_id', $this->currentUserId($request))
            ->where('id', $id)
            ->first();

        if (! $category) {
            abort(403);
        }

        return $category;
    }

    private function currentUserId(Request $request): int
    {
        $userId = (int) ($request->attributes->get('app_user_id') ?? auth()->id() ?? 0);

        if ($userId <= 0) {
            abort(403);
        }

        return $userId;
    }
}
