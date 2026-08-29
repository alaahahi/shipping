<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDrivingAuthorizationTextRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (int) ($this->user()->type_id ?? 0) === 1;
    }

    public function rules(): array
    {
        return [
            'driving_authorization_text' => ['nullable', 'string', 'max:8192'],
        ];
    }

    public function messages(): array
    {
        return [
            'driving_authorization_text.max' => 'نص تخويل القيادة طويل جداً.',
        ];
    }
}
