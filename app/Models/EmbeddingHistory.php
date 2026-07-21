<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmbeddingHistory extends Model
{
    protected $fillable = [

        'text',
        'model',
        'embedding_length',
        'embedding_vector',
        'tokens_used',
        'is_mock'

    ];


    protected $casts = [

        'is_mock' => 'boolean'

    ];


    // Convert array before saving
    public function setEmbeddingVectorAttribute($value)
    {
        $this->attributes['embedding_vector'] = json_encode($value);
    }


    // Convert JSON back to array
    public function getEmbeddingVectorAttribute($value)
    {
        return json_decode($value, true);
    }
}