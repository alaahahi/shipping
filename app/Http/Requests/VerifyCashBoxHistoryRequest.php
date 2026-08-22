<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyCashBoxHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (int) $user->type_id === 1;
    }

    public function rules(): array
    {
        return [
            'transaction_id' => ['required', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_id.required' => 'يجب تحديد الحركة المراد توثيقها.',
            'transaction_id.integer' => 'رقم الحركة غير صالح.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'غير مصرح: توثيق رصيد الصندوق للمسؤول فقط.',
        ], 403));
    }
}
