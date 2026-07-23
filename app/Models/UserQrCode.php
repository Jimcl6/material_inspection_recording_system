<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserQrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'qr_data',
        'employee_id',
        'employment_status',
        'hire_date',
        'contract_end_date',
        'is_active',
        'last_scanned_at',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'contract_end_date' => 'date',
        'last_scanned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('employment_status', $status);
    }

    /**
     * Decode QR data
     */
    public function getDecodedQrDataAttribute(): array
    {
        return json_decode($this->qr_data, true) ?? [];
    }

    /**
     * Check if contract is expiring soon (within 30 days)
     */
    public function isContractExpiringSoon(): bool
    {
        if ($this->employment_status !== 'contractual' || !$this->contract_end_date) {
            return false;
        }

        return $this->contract_end_date->diffInDays(now()) <= 30;
    }

    /**
     * Check if contract has expired
     */
    public function isContractExpired(): bool
    {
        if ($this->employment_status !== 'contractual' || !$this->contract_end_date) {
            return false;
        }

        return $this->contract_end_date->isPast();
    }
}
