<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionCheckpoint extends Model
{
    protected $table = 'inspection_checkpoints';
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

    // Checkpoint position labels (1-4 mapping)
    public const POSITION_LABELS = [
        1 => 'Front 1',
        2 => 'Front 2',
        3 => 'Back 1',
        4 => 'Back 2',
    ];

    public function getPositionLabelAttribute(): string
    {
        return self::POSITION_LABELS[$this->CheckpointNumber] ?? "Checkpoint {$this->CheckpointNumber}";
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductionBatch::class, 'BatchID', 'BatchID');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(InspectionSample::class, 'CheckpointID', 'CheckpointID');
    }
}
