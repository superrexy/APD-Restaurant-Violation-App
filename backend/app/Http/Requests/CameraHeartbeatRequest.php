<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CameraHeartbeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'camera_code' => ['required', 'string', 'exists:cameras,code'],
            'status' => ['nullable', 'string', 'in:active,inactive,maintenance'],
        ];
    }
}
