<?php

namespace App\Http\Requests;

use App\Support\SpreadsheetImportSecurity;
use Illuminate\Foundation\Http\FormRequest;

class ImportDiaphragmWeldingRequest extends FormRequest
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
            'file' => SpreadsheetImportSecurity::rules(),
            'overwrite' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'file.required' => 'Please select an Excel file to import.',
            'file.max' => 'The file size must not exceed 10MB.',
        ];
    }
}
