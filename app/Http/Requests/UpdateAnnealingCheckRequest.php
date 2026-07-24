<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, Rule|array<mixed>|string>
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
            'temperature_setting' => 'nullable|numeric|between:-50,2000',
            'annealing_time' => 'nullable|date_format:H:i',
            'damper_setting' => 'nullable|string|max:100',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'pic_id' => 'sometimes|required|string|max:255',
            'checked_by_id' => 'nullable|string|max:255',
            'verified_by_id' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ];
    }
}
