<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\RagMessage;
use App\Services\DocumentChunkerService;
use App\Services\RagKnowledgebaseService;
use Illuminate\Http\Request;
use Exception;

class DocumentAndRagController extends Controller
{
    protected DocumentChunkerService $chunkerService;
    protected RagKnowledgebaseService $ragService;

    public function __construct(
        DocumentChunkerService $chunkerService,
        RagKnowledgebaseService $ragService
    ) {
        $this->chunkerService = $chunkerService;
        $this->ragService = $ragService;
    }

    /**
     * Upload and chunk document (PDF, TXT, MD, CSV)
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_file' => 'required|file|mimes:pdf,txt,text,md,markdown,csv,json|max:20480', // 20 MB max
            'title' => 'nullable|string|max:200',
            'chunk_size' => 'nullable|integer|min:50|max:1000',
            'overlap' => 'nullable|integer|min:0|max:200',
        ]);

        try {
            $file = $request->file('document_file');
            $title = $request->input('title') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $chunkSize = (int) $request->input('chunk_size', 180);
            $overlap = (int) $request->input('overlap', 30);

            $result = $this->chunkerService->processDocument(
                $file,
                $title,
                $chunkSize,
                $overlap
            );

            return redirect()->route('home', ['tab' => 'documents'])
                ->with('success', "Document \"{$result['document']->title}\" processed into {$result['total_chunks']} sliding-window vector chunks ({$result['total_tokens']} tokens).");

        } catch (Exception $e) {
            return back()->with('error', 'Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete document and its chunks
     */
    public function deleteDocument($id)
    {
        $doc = Document::findOrFail($id);
        $title = $doc->title;
        $doc->delete();

        return redirect()->route('home', ['tab' => 'documents'])
            ->with('success', "Document \"{$title}\" and all associated vector chunks removed.");
    }

    /**
     * Inspect Chunks of a Document (AJAX API)
     */
    public function inspectDocumentChunks($id)
    {
        $doc = Document::with('chunks')->findOrFail($id);

        return response()->json([
            'success' => true,
            'document' => [
                'id' => $doc->id,
                'title' => $doc->title,
                'filename' => $doc->original_filename,
                'file_type' => $doc->file_type,
                'size' => $doc->formatted_file_size,
                'total_chunks' => $doc->total_chunks,
                'total_tokens' => $doc->total_tokens,
            ],
            'chunks' => $doc->chunks->map(function ($c) {
                return [
                    'id' => $c->id,
                    'chunk_index' => $c->chunk_index,
                    'token_count' => $c->token_count,
                    'word_count' => str_word_count($c->content),
                    'content' => $c->content,
                    'has_vector' => !empty($c->embedding_vector),
                    'vector_sample' => is_array($c->embedding_vector) ? array_slice($c->embedding_vector, 0, 5) : [],
                ];
            }),
        ]);
    }

    /**
     * Ask Question to RAG Knowledgebase (AJAX Endpoint)
     */
    public function askRag(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:1000',
            'top_k' => 'nullable|integer|min:1|max:6',
        ]);

        try {
            $topK = (int) $request->input('top_k', 3);
            $result = $this->ragService->askQuestion($request->question, $topK);

            return response()->json($result);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Clear RAG Chat Messages History
     */
    public function clearChatHistory()
    {
        RagMessage::truncate();

        return redirect()->route('home', ['tab' => 'chat'])
            ->with('success', 'RAG Chat History cleared.');
    }

    /**
     * Seed Sample Knowledgebase Documents for 1-Click Instant Testing
     */
    public function seedSampleDocs()
    {
        $sampleDoc1Content = "Laravel 12 Architectural Overview and Modern Features.
Laravel 12 is the latest major evolution of the PHP web application framework. It features enhanced concurrency with Process pipelines, native SQLite in-memory testing out of the box, and streamlined configuration files.
Routing in Laravel 12 is unified under bootstrap/app.php, replacing the traditional kernel and service provider boilerplate. This significantly lowers cognitive load for developers.
Model factories in Laravel 12 have been optimized for high-throughput batch insertion, enabling test suites and seeders to generate thousands of records without memory leaks.
Furthermore, Laravel 12 seamlessly integrates with modern AI services such as OpenAI Text Embeddings, vector databases, and real-time WebSocket broadcasting via Laravel Reverb.";

        $sampleDoc2Content = "OpenAI Text Embeddings & Retrieval-Augmented Generation (RAG) Principles.
Text embeddings measure the relatedness of text strings. They are commonly used for semantic search, recommendation systems, anomaly detection, and diversity measurement.
The text-embedding-3-small and text-embedding-3-large models provide superior retrieval performance while allowing dimensional reduction without losing semantic fidelity.
In a RAG architecture, high-dimensional vector representations are indexed from source documents. When a user submits an inquiry, the query is converted to an embedding vector. Cosine similarity calculates the angular distance between query and document vectors.
The top-matching chunks are retrieved as grounded context and supplied to a language model such as GPT-4o-mini to synthesize a verified, citation-backed response.";

        $this->chunkerService->processDocument(
            $sampleDoc1Content,
            "Laravel 12 Architecture Whitepaper.md",
            80,
            20
        );

        $this->chunkerService->processDocument(
            $sampleDoc2Content,
            "OpenAI Embeddings & RAG Deep Dive.txt",
            80,
            20
        );

        return redirect()->route('home', ['tab' => 'chat'])
            ->with('success', 'Sample Knowledgebase Documents successfully indexed! You can now ask questions in the RAG Chatbot.');
    }
}
