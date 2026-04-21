<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagnetismCheckSheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'item_code' => ['required', 'string', 'max:50'],
            'item_name' => ['required', 'string', 'max:100'],
            'machine_no' => ['required', 'string', 'max:50'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'digits:4'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'item_code.required' => 'Item Code is required.',
            'item_name.required' => 'Item Name is required.',
            'machine_no.required' => 'Machine No. is required.',
            'month.required' => 'Month is required.',
            'month.min' => 'Month must be between 1 and 12.',
            'month.max' => 'Month must be between 1 and 12.',
            'year.required' => 'Year is required.',
            'year.digits' => 'Year must be a 4-digit number.',
        ];
    }
}
