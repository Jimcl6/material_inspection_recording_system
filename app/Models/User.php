<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'employee_id',
        'department_id',
        'position_id',
        'contact_number',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'status' => 'string',
    ];

    protected $appends = ['full_name', 'display_name'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(UserQrCode::class);
    }

    public function loginHistory(): HasMany
    {
        return $this->hasMany(UserLoginHistory::class)->orderBy('login_at', 'desc');
    }

    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->role && $this->role->slug === $role;
        }

        return $this->role && $role->contains('slug', $this->role->slug);
    }

    /**
     * Check if user has specific permission (checks both role AND position permissions)
     */
    public function hasPermission(string $module, string $action): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check role permissions first
        if ($this->role) {
            $hasRolePermission = $this->role->permissions()
                ->where('module', $module)
                ->where('action', $action)
                ->exists();
            
            if ($hasRolePermission) {
                return true;
            }
        }

        // Check position permissions as fallback
        if ($this->position) {
            return $this->position->permissions()
                ->where('module', $module)
                ->where('action', $action)
                ->exists();
        }

        return false;
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->slug === 'super_admin';
    }

    /**
     * Check if user is admin or super admin
     */
    public function isAdmin(): bool
    {
        return $this->role && in_array($this->role->slug, ['admin', 'super_admin']);
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the activities for the user.
     */
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get full name with title
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get user display name with employee ID
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->employee_id 
            ? "{$this->name} ({$this->employee_id})"
            : $this->name;
    }

    /**
     * Record login history
     */
    public function recordLogin(string $ipAddress, string $userAgent = null, string $loginType = 'password', bool $successful = true, string $failureReason = null): UserLoginHistory
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);

        return $this->loginHistory()->create([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'login_type' => $loginType,
            'login_at' => now(),
            'is_successful' => $successful,
            'failure_reason' => $failureReason,
        ]);
    }

    /**
     * Get current active session
     */
    public function getActiveSession(): ?UserLoginHistory
    {
        return $this->loginHistory()
            ->successful()
            ->whereNull('logout_at')
            ->first();
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by department
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope by employment status
     */
    public function scopeByEmploymentStatus($query, $status)
    {
        return $query->whereHas('qrCode', function ($q) use ($status) {
            $q->where('employment_status', $status);
        });
    }
}