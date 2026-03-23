<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $this->currentUserId($request);

        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->paginate(10);

        return view('categories.index', [
            'categories' => $categories,
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

    public function show(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategoryOwnership($request, $category);

        return redirect()->route('categories.edit', $category);
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

        if ($category->budgets()->exists() || $category->transactions()->exists()) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Category cannot be deleted because it is already in use.');
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    private function authorizeCategoryOwnership(Request $request, Category $category): void
    {
        if ((int) $category->user_id !== $this->currentUserId($request)) {
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
