<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CSVUploadRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,txt|max:2048', // Validate that the file is a CSV and not too large (max 2MB)
        ];
    }

    /**
     * Get the custom messages for validation errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'file.required' => 'Please upload a CSV file.',
            'file.mimes' => 'The file must be a CSV or TXT file.',
            'file.max' => 'The file size must not exceed 2MB.',
        ];
    }
}
