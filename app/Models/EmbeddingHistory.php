<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class EmbeddingHistory extends Model
{
    protected $fillable = [

        'text',

        'model',

        'embedding_length',

        'tokens_used',

        'is_mock'

    ];
}
