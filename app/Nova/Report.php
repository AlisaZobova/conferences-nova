<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Oneduo\NovaTimeField\Time;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;
use Zobova\CopyField\CopyField;


class Report extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Report>
     */
    public static $model = \App\Models\Report::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'topic';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'topic'
    ];

    public static $with = ['user', 'conference', 'meeting', 'category'];

    /**
     * Get the fields displayed by the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Announcer', 'user')->rules('required'),

            BelongsTo::make('Conference')->withoutTrashed(),

            Text::make('Topic')
                ->rules('required', 'max:255', 'min:2'),

            DateTime::make('Start Time')->exceptOnForms(),

            Time::make('Start Time')
                ->withMeta(['value' => $this->start_time ? $this->start_time->format('H:i') : ''])
                ->onlyOnForms()
                ->fillUsing(
                    function ($request, $model) {
                        if (!empty($request->conference)) {
                            $conf_date = \App\Models\Conference::find($request->conference)->conf_date;
                            $model['start_time'] = $conf_date->format('Y-m-d') . ' ' . $request->start_time . ':00';
                        }
                    }
                )
                ->sortable()
                ->rules(
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value >= $request->get('end_time')) {
                            $fail('End time should be after start time.');
                        }
                    },
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value < $request->get('end_time')) {

                            $periods = new PeriodCollection();
                            $reportId = $this->id;

                            if (!empty($request->conference)) {
                                $conf_date = \App\Models\Conference::find($request->conference)->conf_date;
                            }

                            $start_time = $conf_date->format('Y-m-d') . ' ' . $value . ':00';
                            $end_time = $conf_date->format('Y-m-d') . ' ' . $request->get('end_time') . ':00';


                            if ($reportId) {
                                $reports = \App\Models\Report::where('conference_id', $request->get('conference'), 'and')
                                    ->where('id', '!=', $reportId)->get();
                            } else {
                                $reports = Report::where('conference_id', $request->get('conference'))->get();
                            }
                            foreach ($reports as $report) {
                                $periods = $periods->add(Period::make($report->start_time, $report->end_time, Precision::MINUTE()));
                            }
                            $boundaries = new PeriodCollection(
                                Period::make(
                                    date('Y-m-d', strtotime($start_time)) .
                                    ' 08:00:00', date('Y-m-d', strtotime($start_time)) . ' 20:00:00', Precision::MINUTE()
                                )
                            );
                            $gaps = $boundaries->subtract($periods);
                            $period = new PeriodCollection(Period::make($start_time, $end_time, Precision::MINUTE()));
                            if (!$periods->isEmpty() && !$period->overlapAll($periods)->isEmpty()) {
                                $closestAfter = null;
                                $closestBefore = null;
                                $closestPeriod = null;
                                $start_date = new \DateTime($start_time);
                                foreach ($gaps as $gap) {
                                    if ($gap->startsAfter($start_date) || $gap->startsAt($start_date) || $gap->contains($start_date)) {
                                        $closestAfter = $gap;
                                        break;
                                    } else if ($gap->startsBefore($start_date)) {
                                        $closestBefore = $gap;
                                    }
                                }

                                if ($closestAfter && $closestBefore) {
                                    if ($closestBefore->end()->getTimestamp() - $period[0]->start()->getTimestamp() < $closestAfter->start()->getTimestamp() - $period[0]->start()->getTimestamp()
                                    ) {
                                        $closestPeriod = $closestBefore;
                                    } else {
                                        $closestPeriod = $closestAfter;
                                    }
                                } else if ($closestAfter && !$closestBefore) {
                                    $closestPeriod = $closestAfter;
                                } else if (!$closestAfter && $closestBefore) {
                                    $closestPeriod = $closestBefore;
                                }

                                $closestStart = $closestPeriod->start()->format('H:i');
                                $closestEnd = $closestPeriod->end()->format('H:i');

                                $fail(
                                    'This time is busy. The closest available period is from ' . $closestStart .
                                    ' to ' . $closestEnd . '.'
                                );
                            }
                        }
                    },
                    function ($attribute, $value, $fail) {
                        if (date('H', strtotime($value)) < 8) {
                            $fail('Conference starts at 8:00.');
                        }
                    },
                ),

            DateTime::make('End Time')->exceptOnForms(),

            Time::make('End Time')
                ->withMeta(['value' => $this->end_time ? $this->end_time->format('H:i') : ''])
                ->onlyOnForms()
                ->fillUsing(
                    function ($request, $model) {
                        if (!empty($request->conference)) {
                            $conf_date = \App\Models\Conference::find($request->conference)->conf_date;
                            $model['end_time'] = $conf_date->format('Y-m-d') . ' ' . $request->end_time . ':00';
                        }
                    }
                )
                ->rules(
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        $start = new \DateTime($request->get('start_time'));
                        $end = new \DateTime($value);
                        $timeDiff = $end->diff($start);
                        $minutes = $timeDiff->h * 60 + $timeDiff->i;
                        if ($minutes > 60 || $timeDiff->format('%Y-%m-%d') != '00-0-0') {
                            $fail('The report should last no more than 60 minutes.');
                        }
                    },
                    function ($attribute, $value, $fail) {
                        if (date('H', strtotime($value)) >= 20 && date('m', strtotime($value)) > 0) {
                            $fail('The conference lasts until 20:00.');
                        }
                    },
                ),
            Text::make('Description')->nullable(),
            File::make('Presentation')->onlyOnDetail(),
            File::make('Presentation')->rules('mimes:ppx,pptx', 'max:10240')->nullable()
                ->storeAs(
                    function (Request $request) {
                        return time() . '_' . $request->presentation->getClientOriginalName();
                    }
                )->onlyOnForms(),

            CopyField::make(
                'Start Url', function () {
                return $this->meeting ? $this->meeting->start_url : '';
            }
            )->onlyOnDetail(),

            CopyField::make(
                'Join Url', function () {
                return $this->meeting ? $this->meeting->join_url : '';
            }
            )->onlyOnDetail(),

            BelongsTo::make('Category')->exceptOnForms(),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
