<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkController;
use App\Http\Controllers\SubtitleAiController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('/profile/update/', WorkController::class);

Route::post('/profile/register/', [WorkController::class, 'checkservice']);



//    Route::post('/ai/analyze', [SubtitleAiController::class, 'analyze'])->name('ai.analyze');
//     Route::post('/ai/chat', [SubtitleAiController::class, 'chat'])->name('ai.chat');

