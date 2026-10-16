<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Exception;

class DocumentChunkerService
{
    protected OpenAIService $openAIService;

    public function __construct(OpenAIService $openAIService)
    {
        $this->openAIService = $openAIService;
    }

    /**
     * Process an uploaded document, chunk it with sliding window, and vectorize chunks
     */
    public function processDocument(
        UploadedFile|string $fileOrPath,
        string $title,
        int $chunkSizeWords = 200,
        int $overlapWords = 30
    ): array {
        $startTime = microtime(true);

        if ($fileOrPath instanceof UploadedFile) {
            $originalName = $fileOrPath->getClientOriginalName();
            $extension = strtolower($fileOrPath->getClientOriginalExtension());
            $sizeBytes = $fileOrPath->getSize();
            $rawContent = $this->extractTextFromFile($fileOrPath->getRealPath(), $extension);
        } else {
            if (strlen($fileOrPath) < 255 && file_exists($fileOrPath)) {
                $originalName = basename($fileOrPath);
                $extension = strtolower(pathinfo($fileOrPath, PATHINFO_EXTENSION) ?: 'txt');
                $sizeBytes = filesize($fileOrPath);
                $rawContent = $this->extractTextFromFile($fileOrPath, $extension);
            } else {
                $originalName = str_contains($title, '.') ? $title : $title . '.txt';
                $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION) ?: 'txt');
                $sizeBytes = strlen($fileOrPath);
                $rawContent = $fileOrPath;
            }
        }

        $rawContent = trim($rawContent);
        if (empty($rawContent)) {
            throw new Exception("Unable to extract readable text from document. Ensure file is not empty or corrupted.");
        }

        // 1. Sliding window chunking
        $chunks = $this->createSlidingWindowChunks($rawContent, $chunkSizeWords, $overlapWords);

        // 2. Create document record
        $document = Document::create([
            'title' => $title ?: pathinfo($originalName, PATHINFO_FILENAME),
            'original_filename' => $originalName,
            'file_type' => $extension ?: 'txt',
            'file_size_bytes' => $sizeBytes,
            'total_chunks' => count($chunks),
            'total_tokens' => 0,
        ]);

        $totalTokens = 0;

        // 3. Vectorize each chunk and save
        foreach ($chunks as $chunk) {
            $embResult = $this->openAIService->generateEmbedding($chunk['content']);
            $tokenCount = $embResult['usage']['total_tokens'] ?? (int) (str_word_count($chunk['content']) * 1.3);
            $totalTokens += $tokenCount;

            DocumentChunk::create([
                'document_id' => $document->id,
                'chunk_index' => $chunk['chunk_index'],
                'content' => $chunk['content'],
                'token_count' => $tokenCount,
                'embedding_vector' => $embResult['embedding'],
                'model' => $embResult['model'] ?? 'text-embedding-3-small',
            ]);
        }

        $document->update(['total_tokens' => $totalTokens]);
        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        return [
            'success' => true,
            'document' => $document,
            'total_chunks' => count($chunks),
            'total_tokens' => $totalTokens,
            'duration_ms' => $durationMs,
        ];
    }

    /**
     * Splits full text into sliding-window overlapping chunks
     */
    public function createSlidingWindowChunks(string $text, int $chunkSize = 200, int $overlap = 30): array
    {
        // Normalize line breaks and multiple spaces
        $text = preg_replace("/\r\n|\r/", "\n", $text);
        $text = preg_replace("/[ \t]+/", " ", $text);

        // Break into words while preserving sentence boundaries
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        $totalWords = count($words);

        if ($totalWords <= $chunkSize) {
            return [[
                'chunk_index' => 0,
                'content' => implode(' ', $words),
                'word_count' => $totalWords,
            ]];
        }

        $chunks = [];
        $startIndex = 0;
        $chunkIndex = 0;
        $step = max(1, $chunkSize - $overlap);

        while ($startIndex < $totalWords) {
            $slice = array_slice($words, $startIndex, $chunkSize);
            $chunkText = implode(' ', $slice);

            $chunks[] = [
                'chunk_index' => $chunkIndex++,
                'content' => $chunkText,
                'word_count' => count($slice),
            ];

            $startIndex += $step;

            // If remaining words are fewer than the overlap, stop
            if ($startIndex >= $totalWords) {
                break;
            }
        }

        return $chunks;
    }

    /**
     * Extracts text from multiple file types (PDF, TXT, CSV, MD)
     */
    public function extractTextFromFile(string $filePath, string $extension): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        if (in_array($extension, ['txt', 'md', 'markdown', 'csv', 'json', 'log'])) {
            return file_get_contents($filePath);
        }

        if ($extension === 'pdf') {
            return $this->extractTextFromPdf($filePath);
        }

        return file_get_contents($filePath);
    }

    /**
     * Pure PHP PDF text extractor
     */
    protected function extractTextFromPdf(string $filePath): string
    {
        $content = file_get_contents($filePath);
        if (!$content) {
            return '';
        }

        $text = '';

        // Extract and decompress FlateDecode streams if present
        if (preg_match_all('#stream[\r\n]+(.*?)[\r\n]+endstream#s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                $uncompressed = @gzuncompress($stream);
                if ($uncompressed !== false) {
                    $text .= ' ' . $this->parsePdfStreamText($uncompressed);
                } else {
                    $text .= ' ' . $this->parsePdfStreamText($stream);
                }
            }
        }

        // If parsed stream is empty, attempt basic regex text extraction
        if (empty(trim($text))) {
            preg_match_all('#\((.*?)\)#s', $content, $parenthesisMatches);
            if (!empty($parenthesisMatches[1])) {
                $text = implode(' ', array_filter($parenthesisMatches[1], fn($s) => strlen(trim($s)) > 2 && preg_match('/^[a-zA-Z0-9\s.,;:!?-]+$/', $s)));
            }
        }

        $cleaned = preg_replace('/[^a-zA-Z0-9\s.,;:!?\'"\-()@#$%&*]/', ' ', $text);
        return preg_replace('/\s+/', ' ', trim($cleaned));
    }

    /**
     * Parse text operators within a PDF stream (BT ... ET)
     */
    protected function parsePdfStreamText(string $stream): string
    {
        $text = '';

        // Extract BT ... ET blocks
        if (preg_match_all('#BT[\r\n\s]+(.*?)[\r\n\s]+ET#s', $stream, $textBlocks)) {
            foreach ($textBlocks[1] as $block) {
                // Match Tj operator: (string) Tj
                if (preg_match_all('#\((.*?)\)\s*Tj#s', $block, $tjMatches)) {
                    $text .= implode(' ', $tjMatches[1]) . "\n";
                }
                // Match TJ array operator: [ (str) -10 (str) ] TJ
                if (preg_match_all('#\[(.*?)\]\s*TJ#s', $block, $tjArrayMatches)) {
                    foreach ($tjArrayMatches[1] as $arrayStr) {
                        preg_match_all('#\((.*?)\)#s', $arrayStr, $items);
                        if (!empty($items[1])) {
                            $text .= implode('', $items[1]) . ' ';
                        }
                    }
                    $text .= "\n";
                }
            }
        }

        return $text;
    }
}
