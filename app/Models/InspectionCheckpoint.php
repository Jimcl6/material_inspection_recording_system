<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionCheckpoint extends Model
{
    protected $table = 'InspectionCheckpoints';
    protected $primaryKey = 'CheckpointID';
    public $timestamps = false;

    protected $fillable = [
        'BatchID',
        'CheckpointNumber',
        'InspectorName_First',
        'Judgement_First',
        'InspectorName_Last',
        'Judgement_Last',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'BatchID', 'BatchID');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(InspectionSample::class, 'CheckpointID', 'CheckpointID');
    }
}
