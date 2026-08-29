<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecodeVinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vin' => ['required', 'string', 'size:17', 'regex:/^[A-HJ-NPR-Z0-9]{17}$/i'],
        ];
    }

    public function messages(): array
    {
        return [
            'vin.required' => 'رقم الشاصي مطلوب.',
            'vin.size' => 'رقم الشاصي يجب أن يكون 17 خانة.',
            'vin.regex' => 'رقم الشاصي غير صالح.',
        ];
    }

    public function vin(): string
    {
        return strtoupper(trim((string) $this->input('vin')));
    }
}
