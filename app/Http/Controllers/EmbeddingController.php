<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OpenAIService;

class EmbeddingController extends Controller
{
    protected $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Show the main page
     */
    public function index()
    {
        $apiStatus = $this->openAIService->getApiStatus();
        
        return view('embedding.index', [
            'apiStatus' => $apiStatus,
            'isMockMode' => $this->openAIService->isMockMode()
        ]);
    }

    /**
     * Generate embedding for a single text
     */
    public function generateEmbedding(Request $request)
    {
        $request->validate([
            'text' => 'required|string|max:5000'
        ]);

        $result = $this->openAIService->generateEmbedding($request->text);

        if (!$result['success']) {
            $error = $result['error'];
            
            // Handle rate limit error
            if (strpos($error, 'rate limit') !== false && isset($result['retry_after'])) {
                $error = "Rate limit exceeded. Please wait " . $result['retry_after'] . " seconds and try again.";
            }
            
            return back()
                ->withInput()
                ->with('error', $error);
        }

        return view('embedding.result', [
            'text' => $request->text,
            'embedding' => $result['embedding'],
            'model' => $result['model'],
            'tokens_used' => $result['usage']['total_tokens'],
            'embedding_length' => count($result['embedding']),
            'is_mock' => $result['is_mock'] ?? false
        ]);
    }

    /**
     * Compare similarity between two texts
     */
    public function compareTexts(Request $request)
    {
        $request->validate([
            'text1' => 'required|string|max:2000',
            'text2' => 'required|string|max:2000'
        ]);

        $result1 = $this->openAIService->generateEmbedding($request->text1);
        
        if (!$result1['success']) {
            $error = $result1['error'];
            if (strpos($error, 'rate limit') !== false && isset($result1['retry_after'])) {
                $error = "Rate limit exceeded. Please wait " . $result1['retry_after'] . " seconds and try again.";
            }
            return back()
                ->withInput()
                ->with('error', $error);
        }

        $result2 = $this->openAIService->generateEmbedding($request->text2);
        
        if (!$result2['success']) {
            $error = $result2['error'];
            if (strpos($error, 'rate limit') !== false && isset($result2['retry_after'])) {
                $error = "Rate limit exceeded. Please wait " . $result2['retry_after'] . " seconds and try again.";
            }
            return back()
                ->withInput()
                ->with('error', $error);
        }

        $similarity = $this->openAIService->cosineSimilarity(
            $result1['embedding'],
            $result2['embedding']
        );

        return view('embedding.comparison', [
            'text1' => $request->text1,
            'text2' => $request->text2,
            'similarity' => $similarity,
            'similarity_percentage' => round($similarity * 100, 2),
            'tokens_used' => $result1['usage']['total_tokens'] + $result2['usage']['total_tokens'],
            'is_mock' => ($result1['is_mock'] ?? false) || ($result2['is_mock'] ?? false)
        ]);
    }

    /**
     * Show demo page
     */
    public function demo()
    {
        $sampleTexts = [
            "The quick brown fox jumps over the lazy dog",
            "A fast brown fox leaps over a sleepy dog",
            "Artificial intelligence is transforming the world",
            "Machine learning algorithms can predict patterns",
            "The weather is beautiful today",
            "It's a sunny and warm day outside"
        ];

        $apiStatus = $this->openAIService->getApiStatus();

        return view('embedding.demo', [
            'sampleTexts' => $sampleTexts,
            'apiStatus' => $apiStatus,
            'isMockMode' => $this->openAIService->isMockMode()
        ]);
    }
    
    /**
     * Clear rate limit (for testing)
     */
    public function clearRateLimit()
    {
        $this->openAIService->clearRateLimit();
        return back()->with('success', 'Rate limit cache cleared successfully.');
    }
    
    /**
     * Check API status (AJAX endpoint)
     */
    public function apiStatus()
    {
        return response()->json($this->openAIService->getApiStatus());
    }
}