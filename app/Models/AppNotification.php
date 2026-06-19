<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\DatabaseNotification;

/**
 * In-app notifications (legacy-style).
 * For Laravel native notifications, the parent class `notifications` table is used.
 */
class AppNotification extends Model
{
    protected $table = 'app_notifications';

    public $timestamps = false;

    protected $fillable = [
        'user_id', 'type', 'title', 'message',
        'reference_type', 'reference_id', 'is_read', 'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read'  => 'boolean',
            'created_at' => 'datetime',
            'read_at'  => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead(): void
    {
        if (! $this->is_read) {
            $this->update(['is_read' => true, 'read_at' => now()]);
        }
    }
}
