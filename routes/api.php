<?php

use App\Http\Controllers\AIChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'supabase.auth'])->group(function (): void {
    Route::post('/ai/chat', [AIChatController::class, 'send']);
    Route::post('/ai/chat/reset', [AIChatController::class, 'reset']);
});

