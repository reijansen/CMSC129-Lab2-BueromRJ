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
        $userId = (int) $request->attributes->get('app_user_id');
        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15);

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = (int) $request->attributes->get('app_user_id');
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:income,expense'],
        ]);

        Category::create([
            ...$payload,
            'user_id' => $userId,
        ]);

        return redirect()->route('categories.index')->with('status', 'Category created.');
    }

    public function destroy(Request $request, int $category): RedirectResponse
    {
        $userId = (int) $request->attributes->get('app_user_id');
        $category = Category::query()->where('user_id', $userId)->findOrFail($category);
        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Category deleted.');
    }
}

