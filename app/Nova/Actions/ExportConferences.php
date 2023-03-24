<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExportConferences extends Action
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
        $fileName = 'conferences-' . time() . '.csv';
        $delimeter = PHP_OS_FAMILY === 'Windows' ? '\\' : '/';
        $path = public_path('export') . $delimeter . $fileName;

        $columns = array('Title', 'Date', 'Address', 'Country', 'Reports', 'Listeners');

        $file = fopen($path, 'w');
        fputcsv($file, $columns);

        foreach ($models as $conference) {

            $listeners = count($conference->users()->whereHas(
                'roles', function($q){
                $q->where('name', 'Listener');
            })->get());

            $row['Title'] = $conference->title;
            $row['Date'] = substr($conference->conf_date, 0, 10);
            $row['Address'] = $conference->latitude && $conference->longitude ?
                'Lat: ' . $conference->latitude . ', Lng: ' . $conference->longitude : '';
            $row['Country'] = $conference->country ? $conference->country->name : '';
            $row['Reports'] = count($conference->reports);
            $row['Listeners'] = $listeners;

            fputcsv($file, $row);
        }

        fclose($file);

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
