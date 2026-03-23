<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('categories.index');
    }

    public function create(): View
    {
        return view('categories.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense,both'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->noContent();
    }

    public function show(string $id): View
    {
        return view('categories.index');
    }

    public function edit(string $id): View
    {
        return view('categories.index');
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense,both'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->noContent();
    }

    public function destroy(string $id)
    {
        return response()->noContent();
    }
}
