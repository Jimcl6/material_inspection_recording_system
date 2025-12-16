<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\TemperatureReading;
use App\Models\User;

class AnnealingCheck extends Model
{
    use SoftDeletes;

    protected $table = 'annealing_checks';
    
    protected $fillable = [
        'item_code',
        'receiving_date',
        'supplier_lot_number',
        'quantity',
        'annealing_date',
        'machine_number',
        'machine_setting',
        'pic_id',
        'checked_by_id',
        'verified_by_id',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'receiving_date' => 'date',
        'annealing_date' => 'date',
        'quantity' => 'integer',
    ];

    /**
     * Get all temperature readings for this check
     */
    public function temperatureReadings(): HasMany
    {
        return $this->hasMany(TemperatureReading::class);
    }

    /**
     * Person In Charge
     */
    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    /**
     * User who checked this record
     */
    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_id');
    }

    /**
     * User who verified this record
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    /**
     * User who created this record
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User who last updated this record
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
