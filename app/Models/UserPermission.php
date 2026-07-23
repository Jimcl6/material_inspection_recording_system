<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'module',
        'action',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    /**
     * Get permission by module and action
     */
    public static function findByModuleAndAction(string $module, string $action): ?self
    {
        return static::where('module', $module)
            ->where('action', $action)
            ->first();
    }

    /**
     * Create or update permission
     */
    public static function createOrUpdate(string $name, string $module, string $action, string $description = null): self
    {
        $slug = strtolower("{$module}.{$action}");
        
        return static::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'module' => $module,
                'action' => $action,
                'description' => $description,
            ]
        );
    }
}
