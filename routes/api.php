<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmbeddingController;

Route::get('/rate-limit-status', function (Request $request) {
    $openAIService = app(App\Services\OpenAIService::class);
    return response()->json($openAIService->getApiStatus());
});

Route::middleware('auth:sanctum')->group(function () {
    // Protected API routes if needed
});