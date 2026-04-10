<?php

namespace App\Http\Requests;

class UpdateDiaphragmWeldingRequest extends StoreDiaphragmWeldingRequest
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
        $rules = parent::rules();
        
        // Add status for updates
        $rules['status'] = ['nullable', 'string', 'in:pending,approved,rejected'];
        $rules['approval_notes'] = ['nullable', 'string'];
        
        return $rules;
    }
}
