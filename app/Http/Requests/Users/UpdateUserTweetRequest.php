<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserTweetRequest extends FormRequest
{
        /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        return $this->merge($this->route()->parameters());
    }

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
        $this->route()->parameters();
        return [
            'status'  => 'required|in:0,1',
            'title'   => 'required|string|max:100',
            'content' => 'required|string|max:255',
            'tweetId' => 'required|numeric',
        ];
    }
}
