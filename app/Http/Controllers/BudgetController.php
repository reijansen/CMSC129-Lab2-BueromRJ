<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BudgetController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $this->currentUserId($request);

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->with('category')
            ->latest()
            ->paginate(10);

        return view('budgets.index', [
            'budgets' => $budgets,
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
        $budget->load('category');

        return view('budgets.show', [
            'budget' => $budget,
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

    private function currentUserId(Request $request): int
    {
        $userId = (int) ($request->attributes->get('app_user_id') ?? auth()->id() ?? 0);

        if ($userId <= 0) {
            abort(403);
        }

        return $userId;
    }
}
