<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDrivingAuthorizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (int) ($this->user()->type_id ?? 0) === 1;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'car_type' => ['nullable', 'string', 'max:255'],
            'car_number' => ['nullable', 'string', 'max:255'],
            'vin' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:100'],
            'created' => ['nullable', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم المخوَّل له',
            'car_type' => 'نوع السيارة',
            'car_number' => 'رقم السيارة',
            'vin' => 'رقم الشاصي',
            'year' => 'الموديل',
            'color' => 'اللون',
            'created' => 'تاريخ التخويل',
        ];
    }
}
