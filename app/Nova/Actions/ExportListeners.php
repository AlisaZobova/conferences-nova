<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExportListeners extends Action
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
        $fileName = 'conference-'. $models[0]->title .'-listeners' . time() . '.csv';
        $delimeter = PHP_OS_FAMILY === 'Windows' ? '\\' : '/';
        $path = public_path('export') . $delimeter . $fileName;

        $listeners = $models[0]->users()->whereHas(
            'roles', function($q){
            $q->where('name', 'Listener');
        })->get();

        $columns = array('Firstname', 'Lastname', 'Birthdate', 'Country', 'Phone', 'Email');

        $file = fopen($path, 'w');
        fputcsv($file, $columns);

        foreach ($listeners as $listener) {

            $row['Firstname'] = $listener->firstname;
            $row['Lastname'] = $listener->lastname;
            $row['Birthdate'] = substr($listener->birthdate, 0, 10);
            $row['Country'] = $listener->country->name;
            $row['Phone'] = $listener->phone;
            $row['Email'] = $listener->email;

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
