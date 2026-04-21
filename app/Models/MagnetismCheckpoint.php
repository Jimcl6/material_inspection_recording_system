<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagnetismCheckpoint extends Model
{
    use HasFactory;

    protected $table = 'magnetism_checkpoints';

    protected $fillable = [
        'checksheet_id',
        'production_date',
        'checkpoint_number',
        'sample1_first',
        'sample2_first',
        'sample3_first',
        'sample4_first',
        'sample5_first',
        'operator_first',
        'judgment_first',
        'sample1_last',
        'sample2_last',
        'sample3_last',
        'sample4_last',
        'sample5_last',
        'operator_last',
        'judgment_last',
        'checked_by',
    ];

    protected $casts = [
        'production_date' => 'date',
        'checkpoint_number' => 'integer',
        'sample1_first' => 'decimal:1',
        'sample2_first' => 'decimal:1',
        'sample3_first' => 'decimal:1',
        'sample4_first' => 'decimal:1',
        'sample5_first' => 'decimal:1',
        'sample1_last' => 'decimal:1',
        'sample2_last' => 'decimal:1',
        'sample3_last' => 'decimal:1',
        'sample4_last' => 'decimal:1',
        'sample5_last' => 'decimal:1',
    ];

    // Tesla standard range for validation
    public const TESLA_MIN = 120;
    public const TESLA_MAX = 150;

    // Checkpoint position labels
    public const POSITION_LABELS = [
        1 => 'Front 1',
        2 => 'Front 2',
        3 => 'Back 1',
        4 => 'Back 2',
    ];

    /**
     * Get the checksheet that owns this checkpoint.
     */
    public function checksheet(): BelongsTo
    {
        return $this->belongsTo(MagnetismChecksheet::class, 'checksheet_id');
    }

    /**
     * Get the position label for this checkpoint.
     */
    public function getPositionLabelAttribute(): string
    {
        return self::POSITION_LABELS[$this->checkpoint_number] ?? "Checkpoint {$this->checkpoint_number}";
    }

    /**
     * Get first inspection samples as array.
     */
    public function getFirstSamplesAttribute(): array
    {
        return [
            $this->sample1_first,
            $this->sample2_first,
            $this->sample3_first,
            $this->sample4_first,
            $this->sample5_first,
        ];
    }

    /**
     * Get last inspection samples as array.
     */
    public function getLastSamplesAttribute(): array
    {
        return [
            $this->sample1_last,
            $this->sample2_last,
            $this->sample3_last,
            $this->sample4_last,
            $this->sample5_last,
        ];
    }

    /**
     * Set first inspection samples from array.
     */
    public function setFirstSamplesFromArray(array $samples): void
    {
        for ($i = 0; $i < 5; $i++) {
            $field = 'sample' . ($i + 1) . '_first';
            $this->$field = $samples[$i] ?? null;
        }
    }

    /**
     * Set last inspection samples from array.
     */
    public function setLastSamplesFromArray(array $samples): void
    {
        for ($i = 0; $i < 5; $i++) {
            $field = 'sample' . ($i + 1) . '_last';
            $this->$field = $samples[$i] ?? null;
        }
    }

    /**
     * Check if all first inspection samples are within Tesla range.
     */
    public function isFirstInspectionValid(): bool
    {
        foreach ($this->first_samples as $sample) {
            if ($sample !== null && ($sample < self::TESLA_MIN || $sample > self::TESLA_MAX)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if all last inspection samples are within Tesla range.
     */
    public function isLastInspectionValid(): bool
    {
        foreach ($this->last_samples as $sample) {
            if ($sample !== null && ($sample < self::TESLA_MIN || $sample > self::TESLA_MAX)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the formatted production date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->production_date ? $this->production_date->format('Y-m-d') : '';
    }
}
