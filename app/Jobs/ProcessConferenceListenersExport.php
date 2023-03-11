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

class ProcessConferenceListenersExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $conference;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Conference $conference)
    {
        $this->conference = $conference;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $fileName = 'conference-'. $this->conference->title .'-listeners' . time() . '.csv';
        $delimeter = PHP_OS_FAMILY === 'Windows' ? '\\' : '/';
        $path = public_path('export') . $delimeter . $fileName;

        $listeners = $this->conference->users()->whereHas(
            'roles', function($q){
            $q->where('name', 'Listener');
        })->get();

        $columns = array('Firstname', 'Lastname', 'Birthdate', 'Country', 'Phone', 'Email');

        $file = fopen($path, 'w');
        fputcsv($file, $columns);

        foreach ($listeners as $listener) {

            $row['Firstname'] = $listener->firstname;
            $row['Lastname'] = $listener->lastname;
            $row['Birthdate'] = $listener->birthdate;
            $row['Country'] = $listener->country->name;
            $row['Phone'] = $listener->phone;
            $row['Email'] = $listener->email;

            fputcsv($file, $row);
        }

        fclose($file);

        FinishedExport::dispatch('/export/' . $fileName);
    }
}
