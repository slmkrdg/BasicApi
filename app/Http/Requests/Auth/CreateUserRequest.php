<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
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
            'name'      => 'required|string|max:155',
            'surname'   => 'required|string|max:100',
            'email'     => 'required|string|email|max:150|unique:users',
            'phone'     => 'required|numeric|digits:10|unique:users',
            'password'  => 'required|string|min:6|confirmed',
        ];
    }
}
