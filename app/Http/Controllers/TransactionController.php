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

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $this->currentUserId($request);

        $transactions = Transaction::query()
            ->where('user_id', $userId)
            ->with(['budget', 'category'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('transactions.index', [
            'transactions' => $transactions,
        ]);
    }

    public function create(Request $request): View
    {
        $userId = $this->currentUserId($request);

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->orderBy('title')
            ->get();

        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return view('transactions.create', [
            'budgets' => $budgets,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $this->currentUserId($request);
        $payload = $this->validateTransactionPayload($request, $userId);

        Transaction::create([
            ...$payload,
            'user_id' => $userId,
        ]);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction created successfully.');
    }

    public function show(Request $request, Transaction $transaction): View
    {
        $this->authorizeTransactionOwnership($request, $transaction);
        $transaction->load(['budget', 'category']);

        return view('transactions.show', [
            'transaction' => $transaction,
        ]);
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        $this->authorizeTransactionOwnership($request, $transaction);
        $userId = $this->currentUserId($request);

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->orderBy('title')
            ->get();

        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        return view('transactions.edit', [
            'transaction' => $transaction,
            'budgets' => $budgets,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransactionOwnership($request, $transaction);
        $payload = $this->validateTransactionPayload($request, $this->currentUserId($request));
        $transaction->update($payload);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorizeTransactionOwnership($request, $transaction);
        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaction deleted successfully.');
    }

    public function trash(Request $request): View
    {
        $userId = $this->currentUserId($request);

        $transactions = Transaction::onlyTrashed()
            ->where('user_id', $userId)
            ->with(['budget', 'category'])
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return view('transactions.trash', [
            'transactions' => $transactions,
        ]);
    }

    public function restore(Request $request, int $id): RedirectResponse
    {
        $transaction = $this->trashedTransactionForUser($request, $id);
        $transaction->restore();

        return redirect()
            ->route('transactions.trash')
            ->with('success', 'Transaction restored successfully.');
    }

    public function forceDelete(Request $request, int $id): RedirectResponse
    {
        $transaction = $this->trashedTransactionForUser($request, $id);

        try {
            $transaction->forceDelete();
        } catch (QueryException) {
            return redirect()
                ->route('transactions.trash')
                ->with('error', 'Transaction cannot be permanently deleted due to a database constraint.');
        }

        return redirect()
            ->route('transactions.trash')
            ->with('success', 'Transaction permanently deleted.');
    }

    private function validateTransactionPayload(Request $request, int $userId): array
    {
        return $request->validate([
            'budget_id' => [
                'required',
                'integer',
                Rule::exists('budgets', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:income,expense'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function authorizeTransactionOwnership(Request $request, Transaction $transaction): void
    {
        if ((int) $transaction->user_id !== $this->currentUserId($request)) {
            abort(403);
        }
    }

    private function trashedTransactionForUser(Request $request, int $id): Transaction
    {
        $transaction = Transaction::onlyTrashed()
            ->where('user_id', $this->currentUserId($request))
            ->where('id', $id)
            ->first();

        if (! $transaction) {
            abort(403);
        }

        return $transaction;
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
