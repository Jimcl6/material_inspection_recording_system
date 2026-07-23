<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $status
 */
class TempRecord extends Model
{
    use HasFactory;

    protected $table = 'temp_records';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'date',
        'model_series',
        'solder_model',
        'line_assigned',
        'control_no',
        'equipment_type',
        'machine_setting_standard',
        'process_assigned',
        'person_in_charge',
        'time_am',
        'temp_am',
        'time_pm',
        'temp_pm',
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
}
