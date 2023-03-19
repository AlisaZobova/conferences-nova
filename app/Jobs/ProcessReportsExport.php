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

class ProcessReportsExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $reports)
    {}

    /**
     * Execute the job.
     */
    public function handle()
    {
        $fileName = 'reports' . time() . '.csv';
        $delimeter = PHP_OS_FAMILY === 'Windows' ? '\\' : '/';
        $path = public_path('export') . $delimeter . $fileName;

        $columns = array('Topic', 'Time', 'Description', 'Comments');

        $file = fopen($path, 'w');
        fputcsv($file, $columns);

        foreach ($this->reports as $report) {

            $row['Topic'] = $report->topic;
            $row['Time'] = $report->start_time . ' - ' . $report->end_time;
//            $row['Time'] = substr($report->start_time, 11, -3) . ' - ' . substr($report->end_time, 11, -3);
            $row['Description'] = $report->description;
            $row['Comments'] = count($report->comments);


            fputcsv($file, $row);
        }

        fclose($file);

        FinishedExport::dispatch('/export/' . $fileName);
    }
}
