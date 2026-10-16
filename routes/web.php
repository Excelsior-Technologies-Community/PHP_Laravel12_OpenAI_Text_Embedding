<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmbeddingController;
use App\Http\Controllers\DocumentAndRagController;

// Main Studio Dashboard
Route::get('/', [EmbeddingController::class, 'index'])->name('home');
Route::get('/demo', [EmbeddingController::class, 'demo'])->name('demo');

// Core Embedding Operations
Route::post('/embedding/generate', [EmbeddingController::class, 'generateEmbedding'])->name('embedding.generate');
Route::post('/embedding/compare', [EmbeddingController::class, 'compareTexts'])->name('embedding.compare');
Route::post('/embedding/search', [EmbeddingController::class, 'search'])->name('embedding.search');

// Smart Document Chunker & File Vectorizer
Route::post('/document/upload', [DocumentAndRagController::class, 'uploadDocument'])->name('document.upload');
Route::delete('/document/{id}', [DocumentAndRagController::class, 'deleteDocument'])->name('document.delete');
Route::get('/document/{id}/chunks', [DocumentAndRagController::class, 'inspectDocumentChunks'])->name('document.chunks');
Route::post('/document/seed-sample', [DocumentAndRagController::class, 'seedSampleDocs'])->name('document.seed.sample');

// RAG Semantic Knowledgebase Chatbot
Route::post('/rag/ask', [DocumentAndRagController::class, 'askRag'])->name('rag.ask');
Route::post('/rag/clear', [DocumentAndRagController::class, 'clearChatHistory'])->name('rag.clear');

// Utility & History routes
Route::get('/clear-rate-limit', [EmbeddingController::class, 'clearRateLimit'])->name('clear.rate.limit');
Route::get('/api-status', [EmbeddingController::class, 'apiStatus'])->name('api.status');
Route::get('/history', [EmbeddingController::class, 'history'])->name('embedding.history');
Route::delete('/history/{id}', [EmbeddingController::class, 'destroy'])->name('embedding.destroy');
