<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserLoginHistory extends Model
{
    use HasFactory;

    protected $table = 'user_login_history';
    
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'login_type',
        'login_at',
        'logout_at',
        'is_successful',
        'failure_reason',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'logout_at' => 'datetime',
        'is_successful' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get session duration in minutes
     */
    public function getSessionDurationAttribute(): ?int
    {
        if (!$this->logout_at) {
            return null;
        }

        return $this->login_at->diffInMinutes($this->logout_at);
    }

    /**
     * Check if session is still active
     */
    public function isSessionActive(): bool
    {
        return $this->is_successful && !$this->logout_at;
    }

    /**
     * Scope for successful logins
     */
    public function scopeSuccessful($query)
    {
        return $query->where('is_successful', true);
    }

    /**
     * Scope for failed logins
     */
    public function scopeFailed($query)
    {
        return $query->where('is_successful', false);
    }

    /**
     * Scope for QR code logins
     */
    public function scopeQrCode($query)
    {
        return $query->where('login_type', 'qr_code');
    }

    /**
     * Scope for password logins
     */
    public function scopePassword($query)
    {
        return $query->where('login_type', 'password');
    }
}
