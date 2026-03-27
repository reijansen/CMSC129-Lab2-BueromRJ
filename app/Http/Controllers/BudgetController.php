<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $this->currentUserId($request);
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'category_id' => $request->query('category_id'),
            'status' => $request->query('status'),
            'period_start_from' => $request->query('period_start_from'),
            'period_end_to' => $request->query('period_end_to'),
        ];

        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->with('category')
            ->when($filters['search'] !== '', function ($query) use ($filters) {
                $query->where(function ($innerQuery) use ($filters) {
                    $innerQuery
                        ->where('title', 'ilike', '%' . $filters['search'] . '%')
                        ->orWhere('notes', 'ilike', '%' . $filters['search'] . '%');
                });
            })
            ->when($filters['category_id'], function ($query) use ($filters, $userId) {
                $query->where('category_id', $filters['category_id'])
                    ->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('user_id', $userId));
            })
            ->when($filters['status'], fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['period_start_from'], fn ($query) => $query->whereDate('period_start', '>=', $filters['period_start_from']))
            ->when($filters['period_end_to'], fn ($query) => $query->whereDate('period_end', '<=', $filters['period_end_to']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('budgets.index', [
            'budgets' => $budgets,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $userId = $this->currentUserId($request);
        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return view('budgets.create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $this->currentUserId($request);
        $payload = $this->validateBudgetPayload($request, $userId);

        Budget::create([
            ...$payload,
            'user_id' => $userId,
        ]);

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Budget created successfully.');
    }

    public function show(Request $request, Budget $budget): View
    {
        $this->authorizeBudgetOwnership($request, $budget);
        $userId = $this->currentUserId($request);
        $budget->load('category');
        $relatedTransactions = Transaction::query()
            ->where('user_id', $userId)
            ->where('budget_id', $budget->id)
            ->with('category')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('budgets.show', [
            'budget' => $budget,
            'relatedTransactions' => $relatedTransactions,
        ]);
    }

    public function edit(Request $request, Budget $budget): View
    {
        $this->authorizeBudgetOwnership($request, $budget);

        $categories = Category::query()
            ->where('user_id', $this->currentUserId($request))
            ->orderBy('name')
            ->get();

        return view('budgets.edit', [
            'budget' => $budget,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorizeBudgetOwnership($request, $budget);
        $payload = $this->validateBudgetPayload($request, $this->currentUserId($request));
        $budget->update($payload);

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Budget updated successfully.');
    }

    public function destroy(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorizeBudgetOwnership($request, $budget);
        $budget->delete();

        return redirect()
            ->route('budgets.index')
            ->with('success', 'Budget deleted successfully.');
    }

    public function trash(Request $request): View
    {
        $userId = $this->currentUserId($request);

        $budgets = Budget::onlyTrashed()
            ->where('user_id', $userId)
            ->with('category')
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return view('budgets.trash', [
            'budgets' => $budgets,
        ]);
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $budget = $this->trashedBudgetForUser($request, $id);
        $budget->restore();

        return redirect()
            ->route('budgets.trash')
            ->with('success', 'Budget restored successfully.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $budget = $this->trashedBudgetForUser($request, $id);

        try {
            $budget->forceDelete();
        } catch (QueryException) {
            return redirect()
                ->route('budgets.trash')
                ->with('error', 'Budget cannot be permanently deleted yet because it is still referenced by other records.');
        }

        return redirect()
            ->route('budgets.trash')
            ->with('success', 'Budget permanently deleted.');
    }

    private function validateBudgetPayload(Request $request, int $userId): array
    {
        return $request->validate([
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'allocated_amount' => ['required', 'numeric', 'min:0'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['required', 'in:active,completed,exceeded,archived'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function authorizeBudgetOwnership(Request $request, Budget $budget): void
    {
        if ((int) $budget->user_id !== $this->currentUserId($request)) {
            abort(403);
        }
    }

    private function trashedBudgetForUser(Request $request, int $id): Budget
    {
        $budget = Budget::onlyTrashed()
            ->where('user_id', $this->currentUserId($request))
            ->where('id', $id)
            ->first();

        if (! $budget) {
            abort(403);
        }

        return $budget;
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
