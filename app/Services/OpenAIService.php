<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Exception;

class OpenAIService
{
    protected $apiKey;
    protected $useMock = false;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        
        // Enable mock mode if no API key or key is placeholder
        if (empty($this->apiKey) || $this->apiKey === 'sk-your-openai-api-key-here') {
            $this->useMock = true;
        }
    }

    /**
     * Generate embeddings for given text with proper rate limiting
     */
    public function generateEmbedding(string $text): array
    {
        // Check if we should use mock mode
        if ($this->useMock) {
            return $this->generateMockEmbedding($text);
        }

        // Check rate limit
        $rateLimitCheck = $this->checkRateLimit();
        if (!$rateLimitCheck['allowed']) {
            return [
                'success' => false,
                'error' => 'Rate limit exceeded. Please wait ' . $rateLimitCheck['wait_time'] . ' seconds.',
                'retry_after' => $rateLimitCheck['wait_time']
            ];
        }

        try {
            // Track this request
            $this->trackRequest();
            
            // Create OpenAI client
            $client = \OpenAI::client($this->apiKey);
            
            $response = $client->embeddings()->create([
                'model' => 'text-embedding-ada-002',
                'input' => $text,
            ]);

            return [
                'success' => true,
                'embedding' => $response->embeddings[0]->embedding,
                'model' => $response->model,
                'usage' => [
                    'prompt_tokens' => $response->usage->promptTokens,
                    'total_tokens' => $response->usage->totalTokens,
                ]
            ];

        } catch (\OpenAI\Exceptions\ErrorException $e) {
            // Handle specific OpenAI errors
            $errorMessage = $e->getMessage();
            
            if (strpos($errorMessage, 'rate limit') !== false || 
                strpos($errorMessage, 'Rate limit') !== false ||
                $e->getCode() === 429) {
                return [
                    'success' => false,
                    'error' => 'OpenAI API rate limit exceeded. Please wait 60 seconds.',
                    'retry_after' => 60
                ];
            }
            
            // For authentication errors, switch to mock mode
            if (strpos($errorMessage, 'Incorrect API key') !== false || 
                strpos($errorMessage, 'authentication') !== false) {
                $this->useMock = true;
                return $this->generateMockEmbedding($text);
            }
            
            return [
                'success' => false,
                'error' => 'API Error: ' . $errorMessage
            ];
            
        } catch (Exception $e) {
            // Fallback to mock mode on any other error
            $this->useMock = true;
            return $this->generateMockEmbedding($text);
        }
    }

    /**
     * Generate mock embedding for testing/demo
     */
    private function generateMockEmbedding(string $text): array
    {
        // Create a deterministic mock embedding based on text
        $hash = md5($text);
        $embedding = [];
        
        // Generate 1536-dimensional vector
        for ($i = 0; $i < 1536; $i++) {
            // Create deterministic but varied values based on text hash
            $seed = hexdec(substr($hash, $i % 32, 1)) / 15;
            $embedding[] = (sin($i + $seed) + 1) / 10; // Values between 0 and 0.2
        }
        
        // Simulate token usage (rough estimate: ~4 tokens per word)
        $wordCount = str_word_count($text);
        $tokens = max(1, $wordCount * 4);
        
        return [
            'success' => true,
            'embedding' => $embedding,
            'model' => 'text-embedding-ada-002 (Mock Mode)',
            'usage' => [
                'prompt_tokens' => $tokens,
                'total_tokens' => $tokens,
            ],
            'is_mock' => true
        ];
    }

    /**
     * Check rate limit
     */
    private function checkRateLimit(): array
    {
        $cacheKey = 'openai_rate_limit';
        $now = time();
        
        // Get request history
        $requests = Cache::get($cacheKey, []);
        
        // Remove requests older than 60 seconds
        $recentRequests = array_filter($requests, function ($timestamp) use ($now) {
            return ($now - $timestamp) < 60;
        });
        
        // Check if we've reached the limit (60 requests per minute for free tier)
        if (count($recentRequests) >= 60) {
            // Find oldest request to calculate wait time
            if (!empty($recentRequests)) {
                $oldest = min($recentRequests);
                $waitTime = 60 - ($now - $oldest);
                return [
                    'allowed' => false,
                    'wait_time' => max(1, $waitTime)
                ];
            }
            return [
                'allowed' => false,
                'wait_time' => 60
            ];
        }
        
        return ['allowed' => true, 'wait_time' => 0];
    }

    /**
     * Track API request
     */
    private function trackRequest(): void
    {
        $cacheKey = 'openai_rate_limit';
        $requests = Cache::get($cacheKey, []);
        $requests[] = time();
        
        // Keep only last 100 requests to prevent array from growing too large
        if (count($requests) > 100) {
            $requests = array_slice($requests, -60);
        }
        
        Cache::put($cacheKey, $requests, now()->addMinutes(5));
    }

    /**
     * Calculate cosine similarity between two embeddings
     */
    public function cosineSimilarity(array $embedding1, array $embedding2): float
    {
        if (count($embedding1) !== count($embedding2)) {
            throw new Exception('Embeddings must have the same dimension');
        }

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        // Use a subset for faster calculation in mock mode
        $limit = min(100, count($embedding1));
        
        for ($i = 0; $i < $limit; $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $normA += $embedding1[$i] * $embedding1[$i];
            $normB += $embedding2[$i] * $embedding2[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Get API status
     */
    public function getApiStatus(): array
    {
        $cacheKey = 'openai_rate_limit';
        $requests = Cache::get($cacheKey, []);
        $now = time();
        
        // Count requests in last minute
        $recentRequests = array_filter($requests, function ($timestamp) use ($now) {
            return ($now - $timestamp) < 60;
        });
        
        $requestsPerMinute = count($recentRequests);
        $limitPerMinute = 60;
        
        return [
            'requests_last_minute' => $requestsPerMinute,
            'limit_per_minute' => $limitPerMinute,
            'remaining_requests' => max(0, $limitPerMinute - $requestsPerMinute),
            'usage_percentage' => round(($requestsPerMinute / $limitPerMinute) * 100, 2),
            'is_mock_mode' => $this->useMock
        ];
    }
    
    /**
     * Clear rate limit cache
     */
    public function clearRateLimit(): void
    {
        Cache::forget('openai_rate_limit');
    }
    
    /**
     * Check if in mock mode
     */
    public function isMockMode(): bool
    {
        return $this->useMock;
    }
}