<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialSubLotTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_type',
        'title',
        'sort_order',
    ];

    public $timestamps = true;

    /**
     * Scope to get titles ordered by sort_order for a material type.
     */
    public function scopeForMaterialType($query, string $materialType)
    {
        return $query->where('material_type', $materialType)->orderBy('sort_order');
    }

    /**
     * Get titles as a simple ordered array for a material type.
     */
    public static function getTitlesForMaterialType(string $materialType): array
    {
        return static::forMaterialType($materialType)->pluck('title')->toArray();
    }
}
