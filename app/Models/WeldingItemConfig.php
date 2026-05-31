<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeldingItemConfig extends Model
{
    protected $fillable = [
        'checksheet_type_id',
        'item_code',
        'item_name',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'validation_rules' => 'array',
        'is_active' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(WeldingChecksheetType::class, 'checksheet_type_id');
    }

    public function checksheets(): HasMany
    {
        return $this->hasMany(WeldingChecksheet::class, 'item_config_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
