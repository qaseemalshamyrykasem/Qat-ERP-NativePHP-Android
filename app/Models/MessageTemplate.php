<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $fillable = ['name', 'type', 'content', 'variables', 'status'];

    protected function casts(): array
    {
        return ['status' => 'boolean'];
    }
}
