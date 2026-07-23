<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MagnetismChecksheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'magnetism_checksheets';

    protected $fillable = [
        'item_code',
        'item_name',
        'machine_no',
        'month',
        'year',
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
    ];

    /**
     * Get all batches for this checksheet.
     */
    public function batches(): HasMany
    {
        return $this->hasMany(MagnetismBatch::class, 'checksheet_id');
    }

    /**
     * Get all checkpoints for this checksheet.
     */
    public function checkpoints(): HasMany
    {
        return $this->hasMany(MagnetismCheckpoint::class, 'checksheet_id');
    }

    /**
     * Get batches for a specific production date.
     */
    public function batchesForDate(string $date): HasMany
    {
        return $this->batches()->where('production_date', $date);
    }

    /**
     * Get checkpoints for a specific production date.
     */
    public function checkpointsForDate(string $date): HasMany
    {
        return $this->checkpoints()->where('production_date', $date);
    }

    /**
     * Get unique production dates for this checksheet.
     */
    public function getProductionDatesAttribute(): array
    {
        return $this->batches()
            ->select('production_date')
            ->distinct()
            ->orderBy('production_date', 'desc')
            ->pluck('production_date')
            ->toArray();
    }

    /**
     * Get the month/year display string.
     */
    public function getMonthYearDisplayAttribute(): string
    {
        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        
        return ($monthNames[$this->month] ?? '') . ' ' . $this->year;
    }
}
