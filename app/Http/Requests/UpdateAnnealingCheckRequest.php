<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateAnnealingCheckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Will be updated with proper authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_code' => 'sometimes|required|string|max:50',
            'receiving_date' => 'sometimes|required|date',
            'supplier_lot_number' => 'sometimes|required|string|max:100',
            'quantity' => 'sometimes|required|integer|min:1',
            'annealing_date' => 'sometimes|required|date|after_or_equal:receiving_date',
            'machine_number' => 'sometimes|required|string|max:50',
            'machine_setting' => 'nullable|string|max:100',
            'pic_id' => 'sometimes|required|exists:users,id',
            'checked_by_id' => 'nullable|exists:users,id',
            'verified_by_id' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
            'temperature_readings' => 'sometimes|array|min:1',
            'temperature_readings.*.id' => 'sometimes|exists:temperature_readings,id',
            'temperature_readings.*.reading_time' => 'required|date_format:H:i',
            'temperature_readings.*.temperature' => 'required|numeric|between:-50,1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'temperature_readings.required' => 'At least one temperature reading is required.',
            'temperature_readings.*.reading_time.required' => 'Reading time is required.',
            'temperature_readings.*.temperature.required' => 'Temperature is required.',
            'temperature_readings.*.temperature.between' => 'Temperature must be between -50 and 1000 degrees.',
        ];
    }
}
