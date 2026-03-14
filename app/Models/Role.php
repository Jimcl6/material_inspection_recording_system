<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(UserPermission::class, 'role_permissions');
    }

    /**
     * Check if role has specific permission
     */
    public function hasPermission(string $module, string $action): bool
    {
        return $this->permissions()
            ->where('module', $module)
            ->where('action', $action)
            ->exists();
    }

    /**
     * Grant permission to role
     */
    public function grantPermission(UserPermission $permission): void
    {
        $this->permissions()->syncWithoutDetaching([$permission->id]);
    }

    /**
     * Revoke permission from role
     */
    public function revokePermission(UserPermission $permission): void
    {
        $this->permissions()->detach($permission->id);
    }

    /**
     * Sync permissions for role
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
}