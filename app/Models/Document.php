<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'entity_type', 'entity_id', 'file_name', 'file_path',
        'file_type', 'file_size', 'description', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'file_size'  => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
