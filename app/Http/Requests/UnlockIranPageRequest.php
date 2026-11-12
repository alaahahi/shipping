<?php

namespace App\Http\Requests;

use App\Services\IranPageAccessService;
use Illuminate\Foundation\Http\FormRequest;

class UnlockIranPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'pin' => 'رمز PIN',
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'أدخل رمز PIN',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $pin = (string) $this->input('pin', '');
            if ($pin === '') {
                return;
            }

            if (!app(IranPageAccessService::class)->pinMatches($pin)) {
                $validator->errors()->add('pin', 'رمز PIN غير صحيح');
            }
        });
    }
}
