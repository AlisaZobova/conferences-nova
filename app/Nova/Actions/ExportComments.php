<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ExportComments extends Action
{
    use InteractsWithQueue, Queueable;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     * @param  \Illuminate\Support\Collection    $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $fileName = 'report-'. $models[0]->topic .'-comments' . time() . '.csv';

        $file = fopen('php://temp/maxmemory:' . (5*1024*1024), 'r+');

        $comments = $models[0]->comments;

        $columns = array('User', 'Date', 'Content');

        fputcsv($file, $columns);

        foreach ($comments as $comment) {

            $row['User'] = $comment->user->firstname . ' ' . $comment->user->lastname;
            $row['Date'] = $comment->publication_date;
            $row['Content'] = $comment->content;

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
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
