<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModificationLog extends Model
{
    protected $table = 'modification_logs';
    protected $primaryKey = 'id';
    public $timestamps = true; // created_at, updated_at

    protected $fillable = [
        'prod_date',
        'col_4m',
        'col_line',
        'model_code',
        'item_for_modification',
        'nature_of_change',
        'col_from',
        'col_to',
        'material_lot_no',
        'start_serial',
        'end_serial',
        'recorded_by',
        'source_of_info',
        'approved_by',
        'job_no_transfer_order',
        'col_remarks',
    ];
}
