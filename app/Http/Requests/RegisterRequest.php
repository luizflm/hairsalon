<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        $cpf_regex = '/^\d{3}\.\d{3}\.\d{3}\-\d{2}$/';

        return [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|min:2',
            'cpf' => ['required', 'regex:'.$cpf_regex],
            'password' => 'required|min:4',
            'password_confirm' => 'required|same:password'
        ];
    }
}
