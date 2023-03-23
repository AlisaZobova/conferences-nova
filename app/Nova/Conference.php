<?php

namespace App\Nova;

use App\Nova\Actions\ExportConferences;
use App\Nova\Actions\ExportListeners;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Zobova\GoogleMaps\GoogleMaps;

class Conference extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Conference>
     */
    public static $model = \App\Models\Conference::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'title'
    ];

    public static $with = ['country', 'category'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            Text::make('Title')
                ->rules('required', 'max:255', 'min:2'),

            Date::make('Date', 'conf_date')
                ->min(now())
                ->sortable()
                ->rules('required', 'after_or_equal:' .
                    date('d.m.Y', strtotime('-1 day', strtotime(today())))),

            GoogleMaps::make('Address')->hideFromIndex(),

            Text::make('Country', function () {
                return $this->country ? $this->country->name : '';
            })->exceptOnForms(),

            BelongsTo::make('Country')->nullable()->onlyOnForms(),

            BelongsTo::make('Category')->nullable()->exceptOnForms(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [
            ExportConferences::make()->onlyOnIndex(),
            ExportListeners::make()->onlyOnDetail()
        ];
    }
}
