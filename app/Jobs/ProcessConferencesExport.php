<?php

namespace App\Jobs;

use App\Events\FinishedExport;
use App\Models\Conference;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessConferencesExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public $conferences)
    {
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $fileName = 'conferences' . time() . '.csv';
        $delimeter = PHP_OS_FAMILY === 'Windows' ? '\\' : '/';
        $path = public_path('export') . $delimeter . $fileName;

        $columns = array('Title', 'Date', 'Address', 'Country', 'Reports', 'Listeners');

        $file = fopen($path, 'w');
        fputcsv($file, $columns);

        foreach ($this->conferences as $conference) {

            $listeners = count($conference->users()->whereHas(
                'roles', function($q){
                $q->where('name', 'Listener');
            })->get());

            $row['Title'] = $conference->title;
            $row['Date'] = $conference->conf_date;
            $row['Address'] = $conference->latitude && $conference->longitude ? 'Lat: ' . $conference->latitude . ', Lng: ' . $conference->longitude : '';
            $row['Country'] = $conference->country ? $conference->country->name : '';
            $row['Reports'] = count($conference->reports);
            $row['Listeners'] = $listeners;

            fputcsv($file, $row);
        }

        fclose($file);

        FinishedExport::dispatch('/export/' . $fileName);

    }
}
