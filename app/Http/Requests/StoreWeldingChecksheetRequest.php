<?php

namespace App\Http\Requests;

use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreWeldingChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checksheet_type_id' => ['required', 'exists:welding_checksheet_types,id'],
            'item_config_id' => ['nullable', 'exists:welding_item_configs,id'],
            'item_name' => ['nullable', 'string', 'max:100'],
            'item_code' => ['nullable', 'string', 'max:50'],
            'month_year' => ['nullable', 'string', 'max:30'],
            'production_date' => ['required', 'date'],
            'machine_no' => ['nullable', 'string', 'max:20'],
            'letter_code' => ['nullable', 'string', 'max:10'],
            'prod_qty' => ['nullable', 'integer', 'min:0'],
            'job_number' => ['nullable', 'string', 'max:50'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'temperature' => ['nullable', 'numeric'],
            'material_fields' => ['nullable', 'array'],
            'material_fields.*' => ['nullable', 'string', 'max:255'],
            'operator_id' => ['nullable', 'exists:users,id'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'checked_by_id' => ['nullable', 'exists:users,id'],
            'operator_name_raw' => ['nullable', 'string', 'max:255'],
            'technician_name_raw' => ['nullable', 'string', 'max:255'],
            'checked_by_name_raw' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'samples' => ['required', 'array', 'min:1'],
            'samples.*.check_item_key' => ['required', 'string', 'max:50'],
            'samples.*.check_item_label' => ['nullable', 'string', 'max:100'],
            'samples.*.requirement_text' => ['nullable', 'string', 'max:150'],
            'samples.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'samples.*.sample_values' => ['nullable', 'array', 'size:5'],
            'samples.*.sample_values.*' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = WeldingChecksheetType::find($this->input('checksheet_type_id'));
            if (!$type) {
                return;
            }

            $itemConfig = null;
            if ($this->filled('item_config_id')) {
                $itemConfig = WeldingItemConfig::find($this->input('item_config_id'));
                if ($itemConfig && (int) $itemConfig->checksheet_type_id !== (int) $type->id) {
                    $validator->errors()->add('item_config_id', 'Selected item code does not belong to the selected checksheet type.');
                    return;
                }
            }

            $this->validateConfiguredItems($validator, $type, $itemConfig);
        });
    }

    protected function validateConfiguredItems(Validator $validator, WeldingChecksheetType $type, ?WeldingItemConfig $itemConfig): void
    {
        $samples = collect($this->input('samples', []))->keyBy('check_item_key');
        $configuredItems = collect($type->check_items ?? []);

        foreach ($configuredItems as $item) {
            $key = $item['key'] ?? null;
            if ($key && !$samples->has($key)) {
                $validator->errors()->add('samples', "Missing sample row for {$item['label']}.");
            }
        }

        $rules = $itemConfig?->validation_rules ?? [];

        if ($type->key === 'diaphragm') {
            $this->validateDiaphragmRules($validator, $samples, $rules);
        }

        if ($type->key === 'casing_tank') {
            $this->validateCasingTankRules($validator, $samples, $rules);
        }
    }

    protected function validateDiaphragmRules(Validator $validator, $samples, array $rules): void
    {
        $this->validateAppearance($validator, $samples);

        $strengthMin = $rules['strength_min'] ?? 0.30;
        $this->validateNumericMinimum($validator, $samples, 'strength', (float) $strengthMin, "Strength must be >= {$strengthMin} kN.");

        if (($rules['measurement_1_type'] ?? null) === 'range') {
            $min = $rules['measurement_1_min'];
            $max = $rules['measurement_1_max'];
            $this->validateNumericRange($validator, $samples, 'measurement_1', (float) $min, (float) $max, "Measurement 1 must be between {$min} and {$max}.");
        }

        if (($rules['measurement_1_type'] ?? null) === 'not_recorded') {
            $this->validateNotRecorded($validator, $samples, 'measurement_1', 'Measurement 1 is not recorded for this item code.');
        }

        if (($rules['circumference_diff_type'] ?? null) === 'max_limit') {
            $maxDiff = (float) $rules['circumference_diff_max'];
            $center = $samples->get('measurement_1')['sample_values'] ?? [];
            foreach (['measurement_2', 'measurement_3', 'measurement_4', 'measurement_5'] as $key) {
                $values = $samples->get($key)['sample_values'] ?? [];
                for ($i = 0; $i < 5; $i++) {
                    if (!$this->isNumericLike($center[$i] ?? null) || !$this->isNumericLike($values[$i] ?? null)) {
                        continue;
                    }
                    $diff = abs((float) $values[$i] - (float) $center[$i]) / 2;
                    if ($diff > $maxDiff) {
                        $validator->errors()->add('samples', "Circumference difference for {$key} sample " . ($i + 1) . " must be <= {$maxDiff}.");
                    }
                }
            }
        }
    }

    protected function validateCasingTankRules(Validator $validator, $samples, array $rules): void
    {
        $this->validateAppearance($validator, $samples);

        if (isset($rules['collapse_depth_min']) && $rules['collapse_depth_min'] !== null) {
            $min = (float) $rules['collapse_depth_min'];
            $this->validateNumericMinimum($validator, $samples, 'collapse_depth', $min, "Collapse depth must be >= {$min} mm.");
        }

        if (isset($rules['collapse_time_min'], $rules['collapse_time_max']) && $rules['collapse_time_min'] !== null && $rules['collapse_time_max'] !== null) {
            $this->validateNumericRange(
                $validator,
                $samples,
                'collapse_time',
                (float) $rules['collapse_time_min'],
                (float) $rules['collapse_time_max'],
                "Collapse time must be between {$rules['collapse_time_min']} and {$rules['collapse_time_max']} sec."
            );
        }
    }

    protected function validateAppearance(Validator $validator, $samples): void
    {
        $appearance = $samples->get('appearance')['sample_values'] ?? [];
        foreach ($appearance as $index => $value) {
            $value = strtoupper(trim((string) $value));
            if ($value === '' || $value === '/' || $value === 'P') {
                continue;
            }

            $validator->errors()->add('samples', 'Appearance sample ' . ($index + 1) . " must be P or /.");
        }
    }

    protected function validateNumericMinimum(Validator $validator, $samples, string $key, float $min, string $message): void
    {
        foreach (($samples->get($key)['sample_values'] ?? []) as $index => $value) {
            if (!$this->isNumericLike($value)) {
                continue;
            }
            if ((float) $value < $min) {
                $validator->errors()->add('samples', $message . ' Sample ' . ($index + 1) . " got {$value}.");
            }
        }
    }

    protected function validateNumericRange(Validator $validator, $samples, string $key, float $min, float $max, string $message): void
    {
        foreach (($samples->get($key)['sample_values'] ?? []) as $index => $value) {
            if (!$this->isNumericLike($value)) {
                continue;
            }
            $num = (float) $value;
            if ($num < $min || $num > $max) {
                $validator->errors()->add('samples', $message . ' Sample ' . ($index + 1) . " got {$value}.");
            }
        }
    }

    protected function validateNotRecorded(Validator $validator, $samples, string $key, string $message): void
    {
        foreach (($samples->get($key)['sample_values'] ?? []) as $index => $value) {
            $value = trim((string) ($value ?? ''));
            if ($value === '' || $value === '/') {
                continue;
            }

            $validator->errors()->add('samples', $message . ' Sample ' . ($index + 1) . " got {$value}.");
        }
    }

    protected function isNumericLike($value): bool
    {
        if ($value === null || $value === '' || $value === '/') {
            return false;
        }

        return is_numeric($value);
    }
}
