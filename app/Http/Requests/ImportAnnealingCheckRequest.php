<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Validation\ValidationRule;

class ImportAnnealingCheckRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:10240', // 10MB max
            ],
            'overwrite' => 'sometimes|boolean',
        ];
    }

    /**
     * Get the file from the request.
     */
    public function getFile(): UploadedFile
    {
        return $this->file('file');
    }

    /**
     * Check if existing records should be overwritten.
     */
    public function shouldOverwrite(): bool
    {
        return $this->boolean('overwrite', false);
    }
}
