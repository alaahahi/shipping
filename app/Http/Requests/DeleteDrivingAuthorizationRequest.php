<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteDrivingAuthorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (int) ($this->user()->type_id ?? 0) === 1;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
        ];
    }
}
