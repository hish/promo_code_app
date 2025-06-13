<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromoCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'nullable|string|unique:promo_codes,code',
            'type' => 'required|in:percentage,value',
            'amount' => 'required|numeric|min:0',
            'max_usage' => 'nullable|integer|min:1',
            'user_max_usage' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',

        ];
    }
}
