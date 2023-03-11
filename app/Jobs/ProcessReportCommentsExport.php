<?php

namespace App\Jobs;

use App\Events\FinishedExport;
use App\Models\Report;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessReportCommentsExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $report;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Report $report)
    {
        $this->report = $report;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $fileName = 'report-'. $this->report->topic .'-comments' . time() . '.csv';
        $delimeter = PHP_OS_FAMILY === 'Windows' ? '\\' : '/';
        $path = public_path('export') . $delimeter . $fileName;

        $comments = $this->report->comments;

        $columns = array('User', 'Date', 'Content');

        $file = fopen($path, 'w');
        fputcsv($file, $columns);

        foreach ($comments as $comment) {

            $row['User'] = $comment->user->firstname . ' ' . $comment->user->lastname;
            $row['Date'] = $comment->publication_date;
            $row['Content'] = $comment->content;

            fputcsv($file, $row);
        }

        fclose($file);

        FinishedExport::dispatch('/export/' . $fileName);
    }
}
