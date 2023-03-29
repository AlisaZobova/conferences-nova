<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExportReports extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $fileName = 'reports-' . time() . '.csv';
        $file = fopen('php://temp/maxmemory:' . (5*1024*1024), 'r+');

        $columns = array('Topic', 'Time', 'Description', 'Comments');

        fputcsv($file, $columns);

        foreach ($models as $report) {

            $row['Topic'] = $report->topic;
            $row['Time'] = $report->start_time . ' - ' . $report->end_time;
            $row['Description'] = $report->description;
            $row['Comments'] = count($report->comments);


            fputcsv($file, $row);
        }

        rewind($file);

        $content = stream_get_contents($file);

        Storage::disk('export')->put($fileName, $content);

        return Action::download('/export/' . $fileName, $fileName);
    }

    /**
     * Get the fields available on the action.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
