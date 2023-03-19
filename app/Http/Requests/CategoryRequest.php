<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CategoryRequest extends FormRequest
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
            'name' => ['required','string','max:255', function ($attribute, $value, $fail) {
                $name = $this->request->get('name');
                if (!$this->request->get('id') || $name != Category::find($this->request->get('id'))->name) {
                    if(Category::where('name', $name)->first()) {
                        $fail('This name already exists');
                    }
                }
            },],
            'ancestor_id' => 'nullable|integer'
        ];
    }
}
