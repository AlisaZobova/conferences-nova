<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RegisterAdditionalRequest extends FormRequest
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
            'firstname' => 'string|max:255|required',
            'lastname' => 'string|max:255|required',
            'phone' => 'string|max:20|required',
            'birthdate' => 'required|date|before_or_equal:' . now(),
            'country' => 'required|integer'
        ];
    }
}
