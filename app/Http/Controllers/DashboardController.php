<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $this->currentUserId($request);

        $totalAllocatedBudget = (float) Budget::query()
            ->where('user_id', $userId)
            ->sum('allocated_amount');

        $totalExpenses = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        $totalIncome = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $remainingBalance = $totalIncome - $totalExpenses;

        $activeBudgetsCount = Budget::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->count();

        $recentTransactions = Transaction::query()
            ->where('user_id', $userId)
            ->with(['budget', 'category'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $activeBudgets = Budget::query()
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->with('category')
            ->orderByDesc('period_end')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();

        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->orderBy('title')
            ->get();

        return view('dashboard.index', [
            'totalAllocatedBudget' => $totalAllocatedBudget,
            'totalExpenses' => $totalExpenses,
            'totalIncome' => $totalIncome,
            'remainingBalance' => $remainingBalance,
            'activeBudgetsCount' => $activeBudgetsCount,
            'recentTransactions' => $recentTransactions,
            'activeBudgets' => $activeBudgets,
            'categories' => $categories,
            'budgets' => $budgets,
        ]);
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
