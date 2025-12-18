<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TorqueRecord extends Model
{
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
    ];
}
