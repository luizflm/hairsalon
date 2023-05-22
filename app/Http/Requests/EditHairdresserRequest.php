<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EditHairdresserRequest extends FormRequest
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
            'name' => 'required|min:2',
            'specialties' => 'required',
            'days' => 'required',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'avatar' => 'file|mimes:jpg,png',
        ];
    }
}
