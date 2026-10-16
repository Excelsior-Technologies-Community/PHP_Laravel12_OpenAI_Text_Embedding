<?php

namespace App\Services;

use App\Models\DocumentChunk;
use App\Models\RagMessage;
use Exception;

class RagKnowledgebaseService
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Answer question based strictly on semantic context retrieved from indexed documents
     */
    public function askQuestion(string $question, int $topK = 3): array
    {
        $startTime = microtime(true);
        $question = trim($question);

        if (empty($question)) {
            throw new Exception("Please provide a question to query your knowledgebase.");
        }

        // 1. Vectorize the question
        $queryEmbedding = $this->openAIService->generateEmbedding($question);
        if (!$queryEmbedding['success']) {
            throw new Exception($queryEmbedding['error'] ?? "Failed to vectorize question.");
        }

        // 2. Retrieve all document chunks
        $chunks = DocumentChunk::with('document')->get();
        if ($chunks->isEmpty()) {
            return [
                'success' => false,
                'question' => $question,
                'answer' => "No indexed documents found in knowledgebase. Please upload a document (PDF, TXT, MD, CSV) first so I can retrieve answers for you.",
                'sources' => [],
                'tokens_used' => $queryEmbedding['usage']['total_tokens'] ?? 0,
                'is_mock' => $queryEmbedding['is_mock'] ?? true,
                'model' => 'gpt-4o-mini',
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ];
        }

        // 3. Compute cosine similarity against all chunks
        $scored = [];
        foreach ($chunks as $chunk) {
            if (empty($chunk->embedding_vector)) {
                continue;
            }

            $sim = $this->openAIService->cosineSimilarity(
                $queryEmbedding['embedding'],
                $chunk->embedding_vector
            );

            $scored[] = [
                'chunk_id' => $chunk->id,
                'document_id' => $chunk->document_id,
                'document_title' => $chunk->document?->title ?? 'Untitled Doc',
                'original_filename' => $chunk->document?->original_filename ?? 'doc.txt',
                'chunk_index' => $chunk->chunk_index,
                'content' => $chunk->content,
                'similarity' => $sim,
                'similarity_percentage' => round($sim * 100, 1),
            ];
        }

        usort($scored, fn ($a, $b) => $b['similarity'] <=> $a['similarity']);
        $topSources = array_slice($scored, 0, $topK);

        // 4. Generate Grounded AI Answer
        $aiResult = $this->generateGroundedAnswer($question, $topSources);

        $totalTokens = ($queryEmbedding['usage']['total_tokens'] ?? 0) + ($aiResult['tokens'] ?? 0);
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        // 5. Persist Chat History
        $ragMessage = RagMessage::create([
            'question' => $question,
            'answer' => $aiResult['answer'],
            'sources' => array_map(fn ($s) => [
                'document_title' => $s['document_title'],
                'chunk_index' => $s['chunk_index'],
                'similarity_percentage' => $s['similarity_percentage'],
                'snippet' => substr($s['content'], 0, 180) . '...',
            ], $topSources),
            'tokens_used' => $totalTokens,
            'model' => $aiResult['model'],
            'is_mock' => $aiResult['is_mock'],
        ]);

        return [
            'success' => true,
            'id' => $ragMessage->id,
            'question' => $question,
            'answer' => $aiResult['answer'],
            'sources' => $topSources,
            'tokens_used' => $totalTokens,
            'is_mock' => $aiResult['is_mock'],
            'model' => $aiResult['model'],
            'duration_ms' => $durationMs,
        ];
    }

    /**
     * Synthesize answer using OpenAI Chat Completion or smart grounded fallback
     */
    protected function generateGroundedAnswer(string $question, array $sources): array
    {
        $isMock = $this->openAIService->isMockMode();
        $apiKey = config('services.openai.api_key');

        // Build context block with citations
        $contextText = "";
        foreach ($sources as $idx => $s) {
            $num = $idx + 1;
            $contextText .= "[Source #{$num}: {$s['document_title']} - Chunk #{$s['chunk_index']} (Match: {$s['similarity_percentage']}%)]\n{$s['content']}\n\n";
        }

        // Live API call if key is present and not mock
        if (!$isMock && !empty($apiKey) && $apiKey !== 'sk-your-openai-api-key-here') {
            try {
                $client = \OpenAI::client($apiKey);
                $systemPrompt = "You are a specialized AI knowledgebase assistant. Answer the user's question accurately using ONLY the provided document sources. Always cite your sources in your answer using [Source #X] or [Document Title - Chunk #X]. If the context does not contain enough information, state that clearly without guessing.";

                $response = $client->chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => "Context:\n" . $contextText . "\nQuestion: " . $question],
                    ],
                    'temperature' => 0.2,
                ]);

                return [
                    'answer' => $response->choices[0]->message->content,
                    'tokens' => $response->usage->totalTokens ?? 0,
                    'model' => 'gpt-4o-mini',
                    'is_mock' => false,
                ];
            } catch (Exception $e) {
                // Fallback to grounded synthesis
            }
        }

        // Grounded synthesis for Mock / Demo Mode
        return $this->buildMockGroundedAnswer($question, $sources);
    }

    /**
     * Smart deterministic grounded response generator for Demo / Testing mode
     */
    protected function buildMockGroundedAnswer(string $question, array $sources): array
    {
        if (empty($sources)) {
            return [
                'answer' => "No relevant context found in documents to answer: \"{$question}\".",
                'tokens' => 50,
                'model' => 'gpt-4o-mini (Demo Mode)',
                'is_mock' => true,
            ];
        }

        $topMatch = $sources[0];
        $sentences = preg_split('/(?<=[.?!])\s+/', $topMatch['content'], -1, PREG_SPLIT_NO_EMPTY);
        $keySentences = array_slice($sentences, 0, min(3, count($sentences)));
        $extractedInfo = implode(' ', $keySentences);

        $answer = "Based on your indexed documents, specifically [{$topMatch['document_title']} - Chunk #{$topMatch['chunk_index']}] (Relevance: {$topMatch['similarity_percentage']}%):\n\n"
            . $extractedInfo . "\n\n";

        if (count($sources) > 1) {
            $secondMatch = $sources[1];
            $secondSentences = preg_split('/(?<=[.?!])\s+/', $secondMatch['content'], -1, PREG_SPLIT_NO_EMPTY);
            if (!empty($secondSentences)) {
                $answer .= "Additionally, [{$secondMatch['document_title']} - Chunk #{$secondMatch['chunk_index']}] notes: \"" . trim($secondSentences[0]) . "\"";
            }
        }

        return [
            'answer' => $answer,
            'tokens' => 120,
            'model' => 'gpt-4o-mini (Demo Mode)',
            'is_mock' => true,
        ];
    }
}
