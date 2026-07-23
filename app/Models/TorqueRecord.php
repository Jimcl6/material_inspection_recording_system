<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TorqueRecord extends Model
{
    use HasFactory;

    protected $table = 'torque_records';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date',
        'model_series',
        'driver_model',
        'driver_type',
        'line_assigned',
        'control_no',
        'screw_type',
        'process_assigned',
        'person_in_charge',
        'time_am',
        'torque_am',
        'time_pm',
        'torque_pm',
        'col_remarks',
        'checked_by',
        'status',
        'submitted_at',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(TorqueReading::class)
            ->orderBy('period')
            ->orderBy('reading_no');
    }
}
