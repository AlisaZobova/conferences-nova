<?php

namespace App\Http\Requests;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Spatie\Period\Period;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Precision;

class ReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'topic' => ['string', 'required', 'min:2', 'max:255'],
            'user_id' => 'integer|required',
            'conference_id' => 'integer|required',
            'start_time' => [
                'required',
                'after_or_equal:' . now(),
                function ($attribute, $value, $fail) {
                    $periods = new PeriodCollection();
                    $end_time = $this->request->get('end_time');
                    $start_time = $this->request->get('start_time');
                    $reportId = $this->request->get('id');
                    if ($reportId) {
                        $reports = Report::where('conference_id', $this->request->get('conference_id'), 'and')->where('id', '!=', $reportId)->get();
                    }
                    else {
                        $reports = Report::where('conference_id', $this->request->get('conference_id'))->get();
                    }
                    foreach ($reports as $report) {
                        $periods = $periods->add(Period::make($report->start_time, $report->end_time, Precision::MINUTE()));
                    }
                    $boundaries = new PeriodCollection(Period::make(date('Y-m-d', strtotime($start_time)) . ' 08:00:00', date('Y-m-d', strtotime($start_time)) . ' 20:00:00', Precision::MINUTE()));
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
                            }
                            else if ($gap->startsBefore($start_date)) {
                                $closestBefore = $gap;
                            }
                        }

                        if ($closestAfter && $closestBefore) {
                            if ($closestBefore->end()->getTimestamp() - $period[0]->start()->getTimestamp() < $closestAfter->start()->getTimestamp() - $period[0]->start()->getTimestamp()) {
                                $closestPeriod = $closestBefore;
                            }
                            else {
                                $closestPeriod = $closestAfter;
                            }
                        }

                        else if ($closestAfter && !$closestBefore) {
                            $closestPeriod = $closestAfter;
                        }

                        else if (!$closestAfter && $closestBefore) {
                            $closestPeriod = $closestBefore;
                        }

                        $closestStart = $closestPeriod->start()->format('H:i');
                        $closestEnd = $closestPeriod->end()->format('H:i');

                        $fail('This time is busy. The closest available period is from ' . $closestStart . ' to ' . $closestEnd . '.');
                    }
                },
                function ($attribute, $value, $fail) {
                    if (date('H', strtotime($value)) < 8) {
                        $fail('Conference starts at 8:00.');
                    }
                },
            ],
            'end_time' => [
                'required',
                'after:start_time',
                function ($attribute, $value, $fail) {
                    $start = new \DateTime($this->request->get('start_time'));
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
            ],
            'description' => 'string|nullable',
            'presentation' => 'nullable|file|mimes:ppx,pptx|max:10240',
            'category_id' => 'integer|nullable'
        ];
    }
}
