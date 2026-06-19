<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    public const ROLE_ADMIN      = 'admin';
    public const ROLE_MANAGER    = 'manager';
    public const ROLE_AGENT      = 'agent';
    public const ROLE_ACCOUNTANT = 'accountant';

    protected $fillable = [
        'username', 'password', 'full_name', 'email', 'phone',
        'role', 'agent_id', 'status', 'last_login',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'    => 'hashed',
            'status'      => 'boolean',
            'last_login'  => 'datetime',
        ];
    }

    // ===== Relationships =====
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'created_by');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'created_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function journalEntries(): HasMany
    {
        return $this->hasMany(JournalEntry::class, 'created_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function appNotifications(): HasMany
    {
        return $this->hasMany(AppNotification::class);
    }

    // ===== Role checks =====
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isManagerOrAbove(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER], true);
    }

    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT;
    }

    public function isAccountant(): bool
    {
        return $this->role === self::ROLE_ACCOUNTANT;
    }

    /**
     * Override Spatie's can() to provide legacy-compatible behavior:
     * admin has all permissions via Gate::before (registered in AuthServiceProvider).
     */
    public function hasModulePermission(string $permission): bool
    {
        return $this->can($permission);
    }
}
