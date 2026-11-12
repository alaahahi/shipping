<?php

namespace App\Http\Requests;

use App\Services\IranPageAccessService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

class DeleteIranResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(IranPageAccessService::class)->isAdmin($this->user());
    }

    public function rules(): array
    {
        return [];
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('الحذف متاح للمدير فقط');
    }
}
