<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{
    protected $fillable = [
        'name', 'phone', 'area', 'balance', 'status',
        'commission_rate', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'balance'         => 'decimal:2',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(AgentSettlement::class);
    }

    public function stock(): HasMany
    {
        return $this->hasMany(AgentStock::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }
}
