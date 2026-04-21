<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MagnetismBatchRequest extends FormRequest
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
            'production_date' => ['required', 'date'],
            'letter_code' => ['required', 'string', 'size:1', 'regex:/^[A-Z]$/'],
            'material_lot_number' => ['required', 'string', 'max:50'],
            'qr_code' => ['required', 'string', 'max:50'],
            'produce_qty' => ['required', 'integer', 'min:1'],
            'job_number' => ['required', 'string', 'max:50'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'production_date.required' => 'Production Date is required.',
            'letter_code.required' => 'Letter Code is required.',
            'letter_code.size' => 'Letter Code must be a single character.',
            'letter_code.regex' => 'Letter Code must be an uppercase letter (A-Z).',
            'material_lot_number.required' => 'Material Lot Number is required.',
            'qr_code.required' => 'QR Code is required.',
            'produce_qty.required' => 'Produce Qty is required.',
            'produce_qty.min' => 'Produce Qty must be at least 1.',
            'job_number.required' => 'Job Number is required.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('letter_code')) {
            $this->merge([
                'letter_code' => strtoupper($this->letter_code),
            ]);
        }
    }
}
