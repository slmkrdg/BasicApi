<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'userName'       => 'required|string|max:155',
            'userPassword'   => 'required|string|max:100',
            'secretKey'      => 'required|string',
            'clientId'       => 'required|string'
        ];
    }
}
