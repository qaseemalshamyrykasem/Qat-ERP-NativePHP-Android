<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    public $timestamps = false;

    protected $table = 'role_permissions';

    protected $fillable = ['role', 'permission_id'];

    /**
     * Get permissions for a role (cached).
     */
    public static function permissionsFor(string $role): array
    {
        return cache()->remember("role_perms.{$role}", 3600, function () use ($role) {
            return static::where('role', $role)
                ->join('permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->pluck('permissions.name')
                ->toArray();
        });
    }

    public static function flushCache(?string $role = null): void
    {
        if ($role) cache()->forget("role_perms.{$role}");
        else cache()->flush();
    }
}
