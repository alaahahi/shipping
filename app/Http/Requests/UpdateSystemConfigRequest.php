<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSystemConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && (int) ($user->type_id ?? 0) !== 10;
    }

    public function rules(): array
    {
        return [
            'first_title_ar' => 'nullable|string|max:255',
            'first_title_kr' => 'nullable|string|max:255',
            'second_title_ar' => 'nullable|string|max:255',
            'second_title_kr' => 'nullable|string|max:255',
            'third_title_ar' => 'nullable|string|max:255',
            'third_title_kr' => 'nullable|string|max:255',
            'default_price_s' => 'nullable|array',
            'default_price_p' => 'nullable|array',
            'usd_to_aed_rate' => 'nullable|numeric|min:0',
            'usd_to_dinar_rate' => 'nullable|numeric|min:0',
            'direct_discount_base' => 'nullable|numeric|min:1',
            'contract_terms' => 'nullable|array',
            'contract_terms_2' => 'nullable|array',
            'external_contract_terms' => 'nullable|array',
            'external_contract_terms_2' => 'nullable|array',
            'contract_template' => 'nullable|in:1,2,3',
            'contract_currency' => 'nullable|in:usd,dinar',
            'primary_color' => 'nullable|string|max:20',
            'wa_enabled' => 'nullable|boolean',
            'wa_tenant' => 'nullable|string|max:100',
            'wa_base_url' => 'nullable|string|max:255',
            'wa_created_by' => 'nullable|string|max:100',
            'wa_notify_client_debt' => 'nullable|boolean',
            'wa_notify_payment_receipt' => 'nullable|boolean',
            'wa_notify_car_added' => 'nullable|boolean',
            'wa_msg_client_debt' => 'nullable|string|max:4096',
            'wa_msg_payment_receipt' => 'nullable|string|max:4096',
            'wa_msg_car_added' => 'nullable|string|max:4096',
            'car_expenses_wallet_user_id' => 'nullable|integer|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'direct_discount_base.numeric' => 'مبلغ أساس الخصم المباشر والمنفست يجب أن يكون رقماً.',
            'direct_discount_base.min' => 'مبلغ أساس الخصم المباشر والمنفست يجب أن يكون أكبر من صفر.',
        ];
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(response()->json([
            'error' => 'غير مسموح الوصول',
        ], 403));
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'error' => 'خطأ في التحقق من البيانات',
            'errors' => $validator->errors(),
        ], 422));
    }
}
