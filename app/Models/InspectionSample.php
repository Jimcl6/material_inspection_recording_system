<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionSample extends Model
{
    protected $table = 'inspection_samples';
    protected $primaryKey = 'SampleID';
    public $timestamps = false;

    protected $fillable = [
        'CheckpointID',
        'SampleOrder',
        'Phase',
        'Value',
    ];

    public function checkpoint(): BelongsTo
    {
        return $this->belongsTo(InspectionCheckpoint::class, 'CheckpointID', 'CheckpointID');
    }
}
