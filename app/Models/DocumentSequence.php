<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSequence extends Model
{
    public $timestamps = false;

    protected $fillable = ['doc_type', 'prefix', 'current_number', 'padding_length'];

    protected function casts(): array
    {
        return [
            'current_number' => 'integer',
            'padding_length' => 'integer',
        ];
    }
}
