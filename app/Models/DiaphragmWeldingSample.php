<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaphragmWeldingSample extends Model
{
    protected $table = 'diaphragm_welding_samples';

    protected $fillable = [
        'checksheet_id',
        'check_item',
        'sample_1',
        'sample_2',
        'sample_3',
        'sample_4',
        'sample_5',
    ];

    /**
     * Valid check item types
     */
    public const CHECK_ITEMS = [
        'collapse_depth',
        'collapse_time',
        'strength',
        'appearance',
        'welding_condition',
        'measurement_1',
        'measurement_2',
        'measurement_3',
        'measurement_4',
        'measurement_5',
    ];

    /**
     * Check item labels for display
     */
    public const CHECK_ITEM_LABELS = [
        'collapse_depth' => 'COLLAPSE - DEPTH (mm)',
        'collapse_time' => 'COLLAPSE - TIME (sec.)',
        'strength' => 'STRENGTH (kN)',
        'appearance' => 'APPEARANCE',
        'welding_condition' => 'WELDING CONDITION',
        'measurement_1' => 'MEASUREMENT 1 - CENTER',
        'measurement_2' => 'MEASUREMENT 2 - UP',
        'measurement_3' => 'MEASUREMENT 3 - LOW',
        'measurement_4' => 'MEASUREMENT 4 - LEFT',
        'measurement_5' => 'MEASUREMENT 5 - RIGHT',
    ];

    /**
     * Get the parent checksheet
     */
    public function checksheet(): BelongsTo
    {
        return $this->belongsTo(DiaphragmWeldingChecksheet::class, 'checksheet_id');
    }

    /**
     * Get display label for check item
     */
    public function getCheckItemLabelAttribute(): string
    {
        return self::CHECK_ITEM_LABELS[$this->check_item] ?? $this->check_item;
    }

    /**
     * Get all sample values as array
     */
    public function getSampleValuesAttribute(): array
    {
        return [
            $this->sample_1,
            $this->sample_2,
            $this->sample_3,
            $this->sample_4,
            $this->sample_5,
        ];
    }

    /**
     * Check if this is an appearance check item
     */
    public function isAppearance(): bool
    {
        return $this->check_item === 'appearance';
    }

    /**
     * Check if this is a strength check item
     */
    public function isStrength(): bool
    {
        return $this->check_item === 'strength';
    }

    /**
     * Check if this is a measurement check item
     */
    public function isMeasurement(): bool
    {
        return str_starts_with($this->check_item, 'measurement_');
    }

    /**
     * Reset all samples (used when appearance fails)
     */
    public function resetSamples(): void
    {
        $this->update([
            'sample_1' => null,
            'sample_2' => null,
            'sample_3' => null,
            'sample_4' => null,
            'sample_5' => null,
        ]);
    }
}
