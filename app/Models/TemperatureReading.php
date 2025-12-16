<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\AnnealingCheck;
use App\Models\User;

class TemperatureReading extends Model
{
    protected $table = 'temperature_readings';
    
    protected $fillable = [
        'annealing_check_id',
        'reading_time',
        'temperature',
        'recorded_by',
    ];

    protected $casts = [
        'reading_time' => 'datetime:H:i',
        'temperature' => 'decimal:2',
    ];

    /**
     * Get the annealing check this reading belongs to
     */
    public function annealingCheck(): BelongsTo
    {
        return $this->belongsTo(AnnealingCheck::class);
    }

    /**
     * User who recorded this reading
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
