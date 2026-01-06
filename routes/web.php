<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmbeddingController;

Route::get('/', [EmbeddingController::class, 'index'])->name('home');
Route::get('/demo', [EmbeddingController::class, 'demo'])->name('demo');

// Embedding routes
Route::post('/embedding/generate', [EmbeddingController::class, 'generateEmbedding'])->name('embedding.generate');
Route::post('/embedding/compare', [EmbeddingController::class, 'compareTexts'])->name('embedding.compare');

// Utility routes
Route::get('/clear-rate-limit', [EmbeddingController::class, 'clearRateLimit'])->name('clear.rate.limit');
Route::get('/api-status', [EmbeddingController::class, 'apiStatus'])->name('api.status');