<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionBatch extends Model
{
    protected $table = 'ProductionBatches';
    protected $primaryKey = 'BatchID';
    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $fillable = [
        'ProductionDate',
        'LetterCode',
        'QRCode',
        'MaterialLotNumber',
        'ProduceQty',
        'JobNumber',
        'TotalQty',
        'Remarks',
    ];

    public function checkpoints(): HasMany
    {
        return $this->hasMany(InspectionCheckpoint::class, 'BatchID', 'BatchID');
    }
}
