<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class TransferWalletTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'integer', 'exists:transactions,id'],
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_id.required' => 'رقم الحركة مطلوب',
            'transaction_id.exists' => 'الحركة غير موجودة',
            'target_user_id.required' => 'القاسة الهدف مطلوبة',
            'target_user_id.exists' => 'القاسة الهدف غير موجودة',
        ];
    }
}
