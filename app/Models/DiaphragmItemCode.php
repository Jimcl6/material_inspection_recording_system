<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiaphragmItemCode extends Model
{
    protected $table = 'diaphragm_item_codes';

    protected $fillable = [
        'item_code',
        'item_name',
        'strength_min',
        'measurement_1_type',
        'measurement_1_min',
        'measurement_1_max',
        'circumference_diff_type',
        'circumference_diff_max',
    ];

    protected $casts = [
        'strength_min' => 'decimal:2',
        'measurement_1_min' => 'decimal:3',
        'measurement_1_max' => 'decimal:3',
        'circumference_diff_max' => 'decimal:2',
    ];

    /**
     * Get all checksheets using this item code
     */
    public function checksheets(): HasMany
    {
        return $this->hasMany(DiaphragmWeldingChecksheet::class, 'item_code', 'item_code');
    }

    /**
     * Check if measurement 1 validation is required
     */
    public function hasMeasurement1Validation(): bool
    {
        return $this->measurement_1_type === 'range';
    }

    /**
     * Check if circumference difference validation is required
     */
    public function hasCircumferenceDiffValidation(): bool
    {
        return $this->circumference_diff_type === 'max_limit';
    }

    /**
     * Validate measurement 1 value
     */
    public function validateMeasurement1($value): bool
    {
        if (!$this->hasMeasurement1Validation()) {
            return true;
        }

        $numValue = (float) $value;
        return $numValue >= $this->measurement_1_min && $numValue <= $this->measurement_1_max;
    }

    /**
     * Validate circumference difference
     * Formula: (Measurement N - Measurement 1) / 2
     */
    public function validateCircumferenceDiff($measurementN, $measurement1): bool
    {
        if (!$this->hasCircumferenceDiffValidation()) {
            return true;
        }

        $diff = abs((float)$measurementN - (float)$measurement1) / 2;
        return $diff <= $this->circumference_diff_max;
    }

    /**
     * Validate strength value
     */
    public function validateStrength($value): bool
    {
        return (float)$value >= $this->strength_min;
    }
}
