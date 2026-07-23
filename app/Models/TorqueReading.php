<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TorqueReading extends Model
{
    protected $fillable = [
        'period',
        'reading_no',
        'torque_value',
    ];

    protected $casts = [
        'reading_no' => 'integer',
        'torque_value' => 'decimal:2',
    ];

    public function torqueRecord(): BelongsTo
    {
        return $this->belongsTo(TorqueRecord::class);
    }
}
