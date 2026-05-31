<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportWeldingChecksheetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'checksheet_type_id' => ['required', 'exists:welding_checksheet_types,id'],
            'item_config_id' => ['nullable', 'exists:welding_item_configs,id'],
            'item_code' => ['nullable', 'string', 'max:50'],
            'item_name' => ['nullable', 'string', 'max:100'],
        ];
    }
}
