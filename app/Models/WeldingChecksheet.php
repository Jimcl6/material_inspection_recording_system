<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeldingChecksheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'checksheet_type_id',
        'item_config_id',
        'item_name',
        'item_code',
        'month_year',
        'production_date',
        'machine_no',
        'letter_code',
        'prod_qty',
        'job_number',
        'quantity',
        'temperature',
        'material_fields',
        'operator_id',
        'technician_id',
        'checked_by_id',
        'operator_name_raw',
        'technician_name_raw',
        'checked_by_name_raw',
        'remarks',
        'status',
        'submitted_at',
        'approved_at',
        'approval_notes',
        'created_by',
        'updated_by',
        'source_file',
        'source_sheet',
        'source_row',
        'legacy_diaphragm_id',
    ];

    protected $casts = [
        'production_date' => 'date',
        'prod_qty' => 'integer',
        'quantity' => 'integer',
        'temperature' => 'decimal:2',
        'material_fields' => 'array',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(WeldingChecksheetType::class, 'checksheet_type_id');
    }

    public function itemConfig(): BelongsTo
    {
        return $this->belongsTo(WeldingItemConfig::class, 'item_config_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(WeldingChecksheetSample::class, 'checksheet_id')->orderBy('sort_order');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getMaterialFieldValue(string $key): ?string
    {
        $fields = $this->material_fields ?? [];
        $value = $fields[$key] ?? null;

        return $value === null ? null : (string) $value;
    }
}
