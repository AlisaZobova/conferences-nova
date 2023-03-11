<?php

namespace App\Observers;

use App\Mail\NewReportComment;
use App\Models\Comment;
use App\Models\Conference;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommentObserver
{
    public function created(Comment $comment) {
        $user = User::find($comment->report->user_id);
        $conference = Conference::find($comment->report->conference_id);
        Mail::to($user->email)->send(new NewReportComment($conference, $comment->report, $user));
    }
}
