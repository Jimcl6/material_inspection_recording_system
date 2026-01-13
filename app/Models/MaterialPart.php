<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialPart extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_type',
        'date',
        'item_block_code',
        'letter_code',
        'main_lot_number',
        'sub_lot_numbers',
        'produced_qty',
        'operator',
        'job_number',
    ];

    protected $casts = [
        'date' => 'date',
        'sub_lot_numbers' => 'array',
        'produced_qty' => 'integer',
    ];

    /**
     * Get all sub-lot numbers as a flat array
     */
    public function getSubLotNumbersArray(): array
    {
        return $this->sub_lot_numbers['sub_lots'] ?? [];
    }

    /**
     * Get total count of sub-lots
     */
    public function getSubLotCountAttribute(): int
    {
        return count($this->getSubLotNumbersArray());
    }

    /**
     * Scope to filter by material type
     */
    public function scopeByMaterialType($query, string $materialType)
    {
        return $query->where('material_type', $materialType);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate = null)
    {
        if ($endDate) {
            return $query->whereBetween('date', [$startDate, $endDate]);
        }
        return $query->whereDate('date', '>=', $startDate);
    }

    /**
     * Scope to search by lot numbers
     */
    public function scopeByLotNumber($query, string $lotNumber)
    {
        return $query->where(function ($q) use ($lotNumber) {
            $q->where('main_lot_number', 'like', "%{$lotNumber}%")
              ->orWhere('sub_lot_numbers', 'like', "%{$lotNumber}%");
        });
    }

    /**
     * Get the next letter code for a given item block code on a specific date
     */
    public static function getNextLetterCode(string $itemBlockCode, string $date): string
    {
        $lastCode = static::where('item_block_code', $itemBlockCode)
            ->where('date', $date)
            ->orderBy('letter_code', 'desc')
            ->value('letter_code');

        if (!$lastCode) {
            return 'A';
        }

        return chr(ord($lastCode) + 1);
    }

    /**
     * Get material types list for dropdown
     */
    public static function getMaterialTypes(): array
    {
        return [
            'ALARM' => 'Alarm',
            'TANK COVER' => 'Tank Cover',
            'SWITCH BLOCK' => 'Switch Block',
            'SALESPARTS' => 'Sales Parts',
            'POWERCORD' => 'Power Cord',
            'MOTOR BLOCK' => 'Motor Block',
            'L-HOSE' => 'L-Hose',
            'FRAME' => 'Frame',
            'FILTER COVER' => 'Filter Cover',
            'CASING' => 'Casing',
        ];
    }
}
