<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('original_filename');
            $table->string('file_type', 20)->default('txt');
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->integer('total_chunks')->default(0);
            $table->integer('total_tokens')->default(0);
            $table->timestamps();
        });

        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->integer('chunk_index')->default(0);
            $table->longText('content');
            $table->integer('token_count')->default(0);
            $table->longText('embedding_vector')->nullable();
            $table->string('model')->default('text-embedding-3-small');
            $table->timestamps();
        });

        Schema::create('rag_messages', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->longText('answer');
            $table->json('sources')->nullable();
            $table->integer('tokens_used')->default(0);
            $table->string('model')->default('gpt-4o-mini');
            $table->boolean('is_mock')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rag_messages');
        Schema::dropIfExists('document_chunks');
        Schema::dropIfExists('documents');
    }
};
