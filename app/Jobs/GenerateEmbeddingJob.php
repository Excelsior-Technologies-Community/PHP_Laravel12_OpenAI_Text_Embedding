<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Cache;

class GenerateEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $text;
    public $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $text, $userId = null)
    {
        $this->text = $text;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $openAIService = app(OpenAIService::class);
        
        // Generate embedding
        $result = $openAIService->generateEmbedding($this->text);
        
        // Store result in cache with user-specific key
        if ($result['success'] && $this->userId) {
            Cache::put(
                'embedding_result_' . $this->userId, 
                $result, 
                now()->addMinutes(10)
            );
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        // Log the failure
        \Log::error('Embedding generation failed: ' . $exception->getMessage());
    }
}