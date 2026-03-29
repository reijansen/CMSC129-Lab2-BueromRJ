<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $userId = (int) ($request->attributes->get('app_user_id') ?? auth()->id() ?? 0);

        if ($userId <= 0) {
            abort(403);
        }

        $user = User::query()->findOrFail($userId);

        return view('profile.show', [
            'user' => $user,
        ]);
    }
}
