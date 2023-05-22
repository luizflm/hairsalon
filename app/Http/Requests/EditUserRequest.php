<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EditUserRequest extends FormRequest
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

    protected function prepareForValidation()
    {
        $this->merge([
            'cpf' => str_replace(['.', '-'], '', $this->cpf),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $loggedUserId = Auth::id();

        return [
            'email' => ['required', 'email', Rule::unique('users')->ignore($loggedUserId)],
            'name' => ['required', 'min:2'],
            'cpf' => ['required', 'digits:11', Rule::unique('users')->ignore($loggedUserId)],
            'password' => ['required', 'min:4'],
            'password_confirm' => ['required', 'same:password'],
        ];
    }
}
