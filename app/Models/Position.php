<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'department_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the permissions for the position.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(UserPermission::class, 'position_permissions', 'position_id', 'permission_id');
    }

    /**
     * Check if position has specific permission
     */
    public function hasPermission(string $module, string $action): bool
    {
        return $this->permissions()
            ->where('module', $module)
            ->where('action', $action)
            ->exists();
    }

    /**
     * Sync permissions for position
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
}
