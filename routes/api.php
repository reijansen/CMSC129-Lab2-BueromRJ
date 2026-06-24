<?php

use App\Http\Controllers\AIChatController;
use App\Http\Controllers\AIAssistantController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::post('/ai/chat', [AIChatController::class, 'send']);
    Route::get('/ai/chat/history', [AIChatController::class, 'history']);
    Route::post('/ai/chat/reset', [AIChatController::class, 'reset']);
    Route::post('/ai/assistant', [AIAssistantController::class, 'send']);
    Route::post('/ai/assistant/confirm', [AIAssistantController::class, 'confirm']);
    Route::post('/ai/assistant/cancel', [AIAssistantController::class, 'cancel']);
});
