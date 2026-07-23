<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeldingChecksheetSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'checksheet_id',
        'check_item_key',
        'check_item_label',
        'requirement_text',
        'sample_values',
        'sort_order',
    ];

    protected $casts = [
        'sample_values' => 'array',
        'sort_order' => 'integer',
    ];

    public function checksheet(): BelongsTo
    {
        return $this->belongsTo(WeldingChecksheet::class, 'checksheet_id');
    }

    public function sampleAt(int $index): ?string
    {
        $values = $this->sample_values ?? [];

        return $values[$index] ?? null;
    }
}
