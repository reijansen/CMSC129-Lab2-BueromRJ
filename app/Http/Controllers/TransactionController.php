<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        return view('transactions.index');
    }

    public function create(): View
    {
        return view('transactions.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'budget_id' => ['required', 'integer', 'exists:budgets,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:income,expense'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->noContent();
    }

    public function show(string $id): View
    {
        return view('transactions.index');
    }

    public function edit(string $id): View
    {
        return view('transactions.index');
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'budget_id' => ['required', 'integer', 'exists:budgets,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:income,expense'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'attachment_path' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->noContent();
    }

    public function destroy(string $id)
    {
        return response()->noContent();
    }
}
