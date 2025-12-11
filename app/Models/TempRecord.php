<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempRecord extends Model
{
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
        'process_assigned',
        'person_in_charge',
        'time_am',
        'temp_am',
        'time_pm',
        'temp_pm',
        'col_remarks',
        'checked_by',
    ];
}
