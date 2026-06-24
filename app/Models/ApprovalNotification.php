<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ApprovalNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'annealing_check_id',
        'module',
        'approvable_type',
        'approvable_id',
        'user_id',
        'type',
        'status',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The annealing check that owns this notification
     */
    public function annealingCheck(): BelongsTo
    {
        return $this->belongsTo(AnnealingCheck::class);
    }

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user that this notification is for
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
