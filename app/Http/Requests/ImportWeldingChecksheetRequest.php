<?php

namespace App\Http\Requests;

use App\Support\SpreadsheetImportSecurity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportWeldingChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => SpreadsheetImportSecurity::rules(),
            'checksheet_type_id' => [
                'required',
                Rule::exists('welding_checksheet_types', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],
            'item_config_id' => [
                'nullable',
                Rule::exists('welding_item_configs', 'id')->where(function ($query) {
                    $query->where('is_active', true)
                        ->where('checksheet_type_id', $this->input('checksheet_type_id'));
                }),
            ],
            'item_code' => ['nullable', 'string', 'max:50'],
            'item_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'checksheet_type_id.required' => 'Select an active checksheet type.',
            'checksheet_type_id.exists' => 'The selected checksheet type is unavailable or inactive.',
            'item_config_id.exists' => 'The selected item code is unavailable, inactive, or belongs to another checksheet type.',
        ];
    }
}
