<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RagMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'sources',
        'tokens_used',
        'model',
        'is_mock',
    ];

    protected $casts = [
        'sources' => 'array',
        'is_mock' => 'boolean',
    ];
}
