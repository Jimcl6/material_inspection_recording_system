<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeldingChecksheetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'material_fields',
        'check_items',
        'import_config',
        'is_active',
    ];

    protected $casts = [
        'material_fields' => 'array',
        'check_items' => 'array',
        'import_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function itemConfigs(): HasMany
    {
        return $this->hasMany(WeldingItemConfig::class, 'checksheet_type_id');
    }

    public function checksheets(): HasMany
    {
        return $this->hasMany(WeldingChecksheet::class, 'checksheet_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function checkItemFor(string $key): ?array
    {
        foreach ($this->check_items ?? [] as $item) {
            if (($item['key'] ?? null) === $key) {
                return $item;
            }
        }

        return null;
    }
}
