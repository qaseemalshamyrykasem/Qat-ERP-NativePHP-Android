<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    protected $fillable = [
        'entity_type', 'entity_id', 'reminder_type', 'title', 'amount',
        'due_date', 'notes', 'sms_enabled', 'sms_sent', 'sms_sent_at',
        'repeat_daily', 'status', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'due_date'     => 'date',
            'sms_enabled'  => 'boolean',
            'sms_sent'     => 'boolean',
            'sms_sent_at'  => 'datetime',
            'repeat_daily' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('due_date', today());
    }
}
