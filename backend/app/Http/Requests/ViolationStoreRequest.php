<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ViolationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'camera_code' => ['required', 'exists:cameras,code'],
            'notes' => ['nullable', 'string'],
            'violation_details' => ['required', 'array', 'min:1'],
            'violation_details.*.violation_code' => ['required', 'exists:violation_types,code'],
            'violation_details.*.confidence_score' => ['nullable', 'numeric', 'between:0,1'],
            'violation_details.*.additional_info' => ['nullable', 'string'],
        ];
    }
}
