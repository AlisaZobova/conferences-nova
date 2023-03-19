<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ConferenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'title' => ['string', 'required', 'min:2', 'max:255', 'regex:/[A-Z].*/'],
            'conf_date' => 'required|after_or_equal:' . now()->format('Y-m-d'),
            'latitude' => 'numeric|between:-90,90|nullable',
            'longitude' => 'numeric|between:-180,180|nullable',
            'category_id' => 'integer|nullable'
        ];
    }
}
