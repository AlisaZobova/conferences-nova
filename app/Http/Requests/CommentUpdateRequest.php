<?php

namespace App\Http\Requests;

use App\Models\Comment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CommentUpdateRequest extends FormRequest
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
            'publication_date' => ['required', 'date',
                function ($attribute, $value, $fail) {
                    $report = Comment::find($this->request->get('id'));
                    $new = new \DateTime($value);
                    $current = new \DateTime($report->publication_date);
                    $timeDiff = $new->diff($current);
                    $minutes = $timeDiff->h * 60 + $timeDiff->i;
                    $seconds = $timeDiff->s;
                    if ($minutes > 10 || ($minutes == 10 && $seconds > 0)) {
                        $fail('Comments can only be edited within 10 minutes.');
                    }
                },],
            'content' => 'required'
        ];
    }
}
