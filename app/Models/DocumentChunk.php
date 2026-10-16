<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'chunk_index',
        'content',
        'token_count',
        'embedding_vector',
        'model',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function setEmbeddingVectorAttribute($value)
    {
        $this->attributes['embedding_vector'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getEmbeddingVectorAttribute($value)
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }
}
