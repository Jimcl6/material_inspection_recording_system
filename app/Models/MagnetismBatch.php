<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MagnetismBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'magnetism_batches';

    protected $fillable = [
        'checksheet_id',
        'production_date',
        'letter_code',
        'material_lot_number',
        'qr_code',
        'produce_qty',
        'job_number',
        'remarks',
    ];

    protected $casts = [
        'production_date' => 'date',
        'produce_qty' => 'integer',
    ];

    /**
     * Get the checksheet that owns this batch.
     */
    public function checksheet(): BelongsTo
    {
        return $this->belongsTo(MagnetismChecksheet::class, 'checksheet_id');
    }

    /**
     * Get the total quantity for the same production date.
     * This is calculated dynamically.
     */
    public function getTotalQtyAttribute(): int
    {
        return self::where('checksheet_id', $this->checksheet_id)
            ->where('production_date', $this->production_date)
            ->sum('produce_qty');
    }

    /**
     * Get letter code for a material lot number within a checksheet.
     * If the lot already exists, return its existing letter.
     * If it's a new lot, return the next available letter.
     */
    public static function getLetterForMaterialLot(int $checksheetId, string $materialLotNumber): ?string
    {
        // Check if this material lot already has a letter assigned in this checksheet
        $existingLetter = self::where('checksheet_id', $checksheetId)
            ->where('material_lot_number', $materialLotNumber)
            ->value('letter_code');

        if ($existingLetter) {
            return strtoupper($existingLetter);
        }

        // New lot - get the next available letter
        return self::getNextAvailableLetter($checksheetId);
    }

    /**
     * Get the next available letter code for a checksheet (across all dates).
     */
    public static function getNextAvailableLetter(int $checksheetId): ?string
    {
        $maxLetter = self::where('checksheet_id', $checksheetId)
            ->orderBy('letter_code', 'desc')
            ->value('letter_code');

        if (!$maxLetter) {
            return 'A';
        }

        $maxLetter = strtoupper($maxLetter);
        if ($maxLetter >= 'Z') {
            return null; // Exhausted
        }

        return chr(ord($maxLetter) + 1);
    }

    /**
     * Get the next available letter code for a given checksheet and date.
     * @deprecated Use getLetterForMaterialLot() instead
     */
    public static function getNextLetterCode(int $checksheetId, string $date): ?string
    {
        return self::getNextAvailableLetter($checksheetId);
    }

    /**
     * Get the formatted production date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->production_date ? $this->production_date->format('Y-m-d') : '';
    }
}
