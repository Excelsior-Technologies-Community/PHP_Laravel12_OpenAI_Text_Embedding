<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'original_filename',
        'file_type',
        'file_size_bytes',
        'total_chunks',
        'total_tokens',
    ];

    public function chunks()
    {
        return $this->hasMany(DocumentChunk::class)->orderBy('chunk_index');
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size_bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
