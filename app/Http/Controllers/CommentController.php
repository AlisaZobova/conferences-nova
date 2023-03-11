<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentCreateRequest;
use App\Http\Requests\CommentUpdateRequest;
use App\Jobs\ProcessReportCommentsExport;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Report $report)
    {
        return Comment::with('user', 'report')->where('report_id', $report->id)->get();
    }

    public function store(CommentCreateRequest $request)
    {
        $data = $request->validated();
        $comment = Comment::create($data);
        return $comment->load('user', 'report');
    }

    public function show(Comment $comment)
    {
        return $comment->load('user', 'report');
    }

    public function update(Comment $comment, CommentUpdateRequest $request)
    {
        $comment->update($request->validated());
        return $comment->load('user', 'report');
    }

    public function export(Report $report) {
        ProcessReportCommentsExport::dispatch($report)->delay(now()->addSeconds(5));;
    }
}
