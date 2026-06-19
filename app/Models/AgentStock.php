<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentStock extends Model
{
    public $timestamps = false;

    protected $fillable = ['agent_id', 'product_id', 'quantity', 'distributed_at', 'notes'];

    protected function casts(): array
    {
        return [
            'quantity'      => 'decimal:2',
            'distributed_at' => 'datetime',
        ];
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
