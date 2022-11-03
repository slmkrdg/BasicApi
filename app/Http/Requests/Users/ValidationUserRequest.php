<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class ValidationUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email'             => 'required_without:phone|string|email|max:150',
            'phone'             => 'required_without:email|numeric|digits:10',
            'verificationCode'  => 'required|string|min:10',
        ];
    }
}
