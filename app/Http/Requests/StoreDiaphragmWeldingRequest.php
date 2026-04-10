<?php

namespace App\Http\Requests;

use App\Models\DiaphragmItemCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDiaphragmWeldingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'item_name' => ['nullable', 'string', 'max:100'],
            'item_code' => ['nullable', 'string', 'max:50'],
            'month_year' => ['nullable', 'string', 'max:20'],
            'production_date' => ['required', 'date'],
            'lasermark_lot_number' => ['nullable', 'string', 'max:50'],
            'machine_no' => ['nullable', 'string', 'max:10'],
            'letter_code' => ['nullable', 'string', 'max:5'],
            'df_rubber_lot' => ['nullable', 'string', 'max:50'],
            'center_plate_a_lot' => ['nullable', 'string', 'max:50'],
            'center_plate_b_lot' => ['nullable', 'string', 'max:50'],
            'prod_qty' => ['nullable', 'integer', 'min:0'],
            'jo_number' => ['nullable', 'string', 'max:50'],
            'temperature' => ['nullable', 'numeric'],
            'operator_id' => ['nullable', 'exists:users,id'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'checked_by_id' => ['nullable', 'exists:users,id'],
            'remarks' => ['nullable', 'string'],
            
            // Samples validation
            'samples' => ['required', 'array'],
            'samples.*.check_item' => ['required', 'string', 'in:collapse_depth,collapse_time,strength,appearance,welding_condition,measurement_1,measurement_2,measurement_3,measurement_4,measurement_5'],
            'samples.*.sample_1' => ['nullable', 'string', 'max:20'],
            'samples.*.sample_2' => ['nullable', 'string', 'max:20'],
            'samples.*.sample_3' => ['nullable', 'string', 'max:20'],
            'samples.*.sample_4' => ['nullable', 'string', 'max:20'],
            'samples.*.sample_5' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function (Validator $validator) {
            $this->validateItemCodeRules($validator);
        });
    }

    /**
     * Validate based on item code specific rules
     */
    protected function validateItemCodeRules(Validator $validator)
    {
        $itemCode = $this->input('item_code');
        $samples = $this->input('samples', []);

        if (!$itemCode) {
            return;
        }

        $itemCodeConfig = DiaphragmItemCode::where('item_code', $itemCode)->first();
        
        if (!$itemCodeConfig) {
            // No config found - apply default rules (strength >= 0.30)
            $itemCodeConfig = new DiaphragmItemCode([
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'circumference_diff_type' => 'data_recording',
            ]);
        }

        // Get samples indexed by check_item
        $samplesIndexed = [];
        foreach ($samples as $sample) {
            $samplesIndexed[$sample['check_item']] = $sample;
        }

        // Validate Appearance - must be P only (F triggers reset)
        if (isset($samplesIndexed['appearance'])) {
            $appearance = $samplesIndexed['appearance'];
            for ($i = 1; $i <= 5; $i++) {
                $value = strtoupper(trim($appearance["sample_{$i}"] ?? ''));
                if ($value !== '' && $value !== 'P' && $value !== '/') {
                    if ($value === 'F') {
                        $validator->errors()->add(
                            "samples.appearance.sample_{$i}",
                            "Appearance FAIL detected. All samples must be reset and redone."
                        );
                    } else {
                        $validator->errors()->add(
                            "samples.appearance.sample_{$i}",
                            "Appearance must be 'P' (Pass) or '/' (Not Applicable)."
                        );
                    }
                }
            }
        }

        // Validate Strength
        if (isset($samplesIndexed['strength'])) {
            $strength = $samplesIndexed['strength'];
            for ($i = 1; $i <= 5; $i++) {
                $value = $strength["sample_{$i}"] ?? '';
                if ($value !== '' && $value !== '/' && is_numeric($value)) {
                    if ((float)$value < $itemCodeConfig->strength_min) {
                        $validator->errors()->add(
                            "samples.strength.sample_{$i}",
                            "Strength must be >= {$itemCodeConfig->strength_min} kN. Got: {$value}"
                        );
                    }
                }
            }
        }

        // Validate Measurement 1 (Center) - if range type
        if ($itemCodeConfig->hasMeasurement1Validation() && isset($samplesIndexed['measurement_1'])) {
            $measurement1 = $samplesIndexed['measurement_1'];
            for ($i = 1; $i <= 5; $i++) {
                $value = $measurement1["sample_{$i}"] ?? '';
                if ($value !== '' && $value !== '/' && is_numeric($value)) {
                    if (!$itemCodeConfig->validateMeasurement1($value)) {
                        $validator->errors()->add(
                            "samples.measurement_1.sample_{$i}",
                            "Measurement 1 (Center) must be between {$itemCodeConfig->measurement_1_min} and {$itemCodeConfig->measurement_1_max}. Got: {$value}"
                        );
                    }
                }
            }
        }

        // Validate Measurements 2-5 (Circumference difference) - if max_limit type
        if ($itemCodeConfig->hasCircumferenceDiffValidation() && isset($samplesIndexed['measurement_1'])) {
            $measurement1 = $samplesIndexed['measurement_1'];
            
            for ($m = 2; $m <= 5; $m++) {
                $measurementKey = "measurement_{$m}";
                if (!isset($samplesIndexed[$measurementKey])) {
                    continue;
                }
                
                $measurementN = $samplesIndexed[$measurementKey];
                
                for ($i = 1; $i <= 5; $i++) {
                    $centerValue = $measurement1["sample_{$i}"] ?? '';
                    $circumValue = $measurementN["sample_{$i}"] ?? '';
                    
                    if ($centerValue !== '' && $circumValue !== '' && 
                        $centerValue !== '/' && $circumValue !== '/' &&
                        is_numeric($centerValue) && is_numeric($circumValue)) {
                        
                        if (!$itemCodeConfig->validateCircumferenceDiff($circumValue, $centerValue)) {
                            $diff = abs((float)$circumValue - (float)$centerValue) / 2;
                            $validator->errors()->add(
                                "samples.{$measurementKey}.sample_{$i}",
                                "Circumference difference exceeds limit. Formula: (|{$circumValue} - {$centerValue}|) / 2 = " . 
                                number_format($diff, 3) . ". Must be <= {$itemCodeConfig->circumference_diff_max}"
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'production_date.required' => 'Production date is required.',
            'samples.required' => 'Sample data is required.',
        ];
    }
}
