<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaphragmWeldingChecksheet extends Model
{
    use SoftDeletes;

    protected $table = 'diaphragm_welding_checksheets';

    protected $fillable = [
        'item_name',
        'item_code',
        'month_year',
        'production_date',
        'lasermark_lot_number',
        'machine_no',
        'letter_code',
        'df_rubber_lot',
        'center_plate_a_lot',
        'center_plate_b_lot',
        'prod_qty',
        'jo_number',
        'temperature',
        'operator_id',
        'technician_id',
        'checked_by_id',
        'remarks',
        'status',
        'submitted_at',
        'approved_at',
        'approval_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'production_date' => 'date',
        'prod_qty' => 'integer',
        'temperature' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Get all welding samples for this checksheet
     */
    public function samples(): HasMany
    {
        return $this->hasMany(DiaphragmWeldingSample::class, 'checksheet_id');
    }

    /**
     * Get the item code configuration
     */
    public function itemCodeConfig(): BelongsTo
    {
        return $this->belongsTo(DiaphragmItemCode::class, 'item_code', 'item_code');
    }

    /**
     * Operator user
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    /**
     * Technician user
     */
    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Checked by user
     */
    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_id');
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

    /**
     * Get sample by check item type
     */
    public function getSampleByType(string $checkItem): ?DiaphragmWeldingSample
    {
        return $this->samples->where('check_item', $checkItem)->first();
    }

    /**
     * Check if any appearance sample has failed
     */
    public function hasAppearanceFail(): bool
    {
        $appearanceSample = $this->getSampleByType('appearance');
        if (!$appearanceSample) {
            return false;
        }

        $samples = [
            $appearanceSample->sample_1,
            $appearanceSample->sample_2,
            $appearanceSample->sample_3,
            $appearanceSample->sample_4,
            $appearanceSample->sample_5,
        ];

        foreach ($samples as $sample) {
            if (strtoupper($sample) === 'F') {
                return true;
            }
        }

        return false;
    }
}
