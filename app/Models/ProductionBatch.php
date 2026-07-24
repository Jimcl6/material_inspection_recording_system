<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $BatchID
 * @property string|null $QRCode
 * @property string|null $ProductionDate
 * @property string|null $LetterCode
 * @property string|null $MaterialLotNumber
 * @property int|float|string|null $ProduceQty
 * @property string|null $JobNumber
 * @property int|float|string|null $TotalQty
 * @property string|null $Remarks
 * @property string|null $ItemName
 * @property string|null $ItemCode
 */
class ProductionBatch extends Model
{
    protected $table = 'productionbatches';

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
        'ItemName',
        'ItemCode',
    ];

    public function checkpoints(): HasMany
    {
        return $this->hasMany(InspectionCheckpoint::class, 'BatchID', 'BatchID');
    }
}
