<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\RagMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DocumentAndRagTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_with_studio_tabs_and_metrics(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('AI Knowledge Studio');
        $response->assertSee('Ask My Docs (RAG Chatbot)');
        $response->assertSee('Smart Document Chunker');
        $response->assertSee('Indexed Chunks');
    }

    public function test_upload_and_chunk_txt_document(): void
    {
        $fileContent = str_repeat("Artificial intelligence and machine learning empower modern semantic search systems. Vector embeddings convert textual strings into dense floating-point arrays for similarity matching. ", 15);
        $file = UploadedFile::fake()->createWithContent('ai_concepts.txt', $fileContent);

        $response = $this->post(route('document.upload'), [
            'document_file' => $file,
            'title' => 'AI Concepts Guide',
            'chunk_size' => 50,
            'overlap' => 10,
        ]);

        $response->assertRedirect(route('home', ['tab' => 'documents']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('documents', [
            'title' => 'AI Concepts Guide',
            'original_filename' => 'ai_concepts.txt',
            'file_type' => 'txt',
        ]);

        $document = Document::first();
        $this->assertNotNull($document);
        $this->assertGreaterThan(1, $document->total_chunks);
        $this->assertGreaterThan(0, $document->total_tokens);

        $chunk = DocumentChunk::where('document_id', $document->id)->first();
        $this->assertNotNull($chunk);
        $this->assertIsArray($chunk->embedding_vector);
        $this->assertCount(1536, $chunk->embedding_vector);
    }

    public function test_inspect_document_chunks_api(): void
    {
        $doc = Document::create([
            'title' => 'Inspection Test Doc',
            'original_filename' => 'inspect.md',
            'file_type' => 'md',
            'file_size_bytes' => 1024,
            'total_chunks' => 2,
            'total_tokens' => 120,
        ]);

        DocumentChunk::create([
            'document_id' => $doc->id,
            'chunk_index' => 0,
            'content' => 'First paragraph of inspection test content.',
            'token_count' => 60,
            'embedding_vector' => array_fill(0, 1536, 0.05),
            'model' => 'text-embedding-3-small',
        ]);

        $response = $this->get(route('document.chunks', $doc->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'document' => ['id', 'title', 'filename', 'file_type', 'total_chunks'],
            'chunks' => [
                '*' => ['id', 'chunk_index', 'token_count', 'word_count', 'content', 'has_vector', 'vector_sample'],
            ],
        ]);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_ask_rag_chatbot_with_grounded_answer(): void
    {
        $doc = Document::create([
            'title' => 'Framework Documentation',
            'original_filename' => 'framework.txt',
            'file_type' => 'txt',
            'file_size_bytes' => 2048,
            'total_chunks' => 1,
            'total_tokens' => 80,
        ]);

        DocumentChunk::create([
            'document_id' => $doc->id,
            'chunk_index' => 0,
            'content' => 'Laravel 12 features unified routing and high-throughput model factories for fast database seeding.',
            'token_count' => 80,
            'embedding_vector' => array_fill(0, 1536, 0.08),
            'model' => 'text-embedding-3-small',
        ]);

        $response = $this->postJson(route('rag.ask'), [
            'question' => 'What does Laravel 12 feature?',
            'top_k' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'question',
            'answer',
            'sources' => [
                '*' => ['chunk_id', 'document_title', 'chunk_index', 'similarity_percentage', 'content'],
            ],
            'tokens_used',
            'model',
            'duration_ms',
        ]);

        $this->assertDatabaseHas('rag_messages', [
            'question' => 'What does Laravel 12 feature?',
        ]);
    }

    public function test_seed_sample_knowledgebase(): void
    {
        $response = $this->post(route('document.seed.sample'));

        $response->assertRedirect(route('home', ['tab' => 'chat']));
        $response->assertSessionHas('success');

        $this->assertEquals(2, Document::count());
        $this->assertGreaterThan(0, DocumentChunk::count());
    }

    public function test_clear_rag_chat_history(): void
    {
        RagMessage::create([
            'question' => 'Old question?',
            'answer' => 'Old answer.',
            'tokens_used' => 50,
            'model' => 'gpt-4o-mini',
            'is_mock' => true,
        ]);

        $response = $this->post(route('rag.clear'));

        $response->assertRedirect(route('home', ['tab' => 'chat']));
        $this->assertEquals(0, RagMessage::count());
    }

    public function test_delete_document_and_cascading_chunks(): void
    {
        $doc = Document::create([
            'title' => 'Delete Me',
            'original_filename' => 'delete_me.txt',
            'file_type' => 'txt',
            'file_size_bytes' => 500,
            'total_chunks' => 1,
            'total_tokens' => 25,
        ]);

        DocumentChunk::create([
            'document_id' => $doc->id,
            'chunk_index' => 0,
            'content' => 'Content to be deleted.',
            'token_count' => 25,
            'embedding_vector' => array_fill(0, 1536, 0.01),
            'model' => 'text-embedding-3-small',
        ]);

        $response = $this->delete(route('document.delete', $doc->id));

        $response->assertRedirect(route('home', ['tab' => 'documents']));
        $this->assertDatabaseMissing('documents', ['id' => $doc->id]);
        $this->assertDatabaseMissing('document_chunks', ['document_id' => $doc->id]);
    }
}
