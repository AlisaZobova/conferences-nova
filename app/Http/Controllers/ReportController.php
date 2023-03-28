<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Http\Requests\ReportUpdateRequest;
use App\Jobs\ProcessReportsExport;
use App\Models\Report;
use App\Services\ZoomMeetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        return $this->getFilteredReports($request)->orderBy('start_time', 'DESC')->paginate(12);
    }

    public function getFilteredReports(Request $request)
    {
        $reports = Report::with('comments');

        $count = $request->query('page') ? 1 : 0;

        if (count($request->query()) > $count) {
            $reports->whereHas(
                'conference', function ($query) {
                    $query->whereDate('conf_date', '>=', date("Y-m-d"));
                }
            );
        }

        foreach ($request->query() as $key => $value) {
            if ($key === 'from') {
                $reports->whereTime('start_time', '>=', $value);
            }
            if ($key === 'to') {
                $reports->whereTime('end_time', '<=', $value);
            }
            if ($key === 'duration') {
                $range = explode('-', $value);
                $reports->whereRaw("TIMESTAMPDIFF(minute, start_time, end_time) BETWEEN " . $range[0] . " AND " . $range[1]);
            }
            if ($key === 'category') {
                $categories = explode(',', $value);
                $reports->whereIn('category_id', $categories);
            }
        }
        return $reports;
    }

    public function search(Request $request)
    {
        if ($request->query('topic')) {
            $reports = Report::whereRaw("UPPER(topic) LIKE '%" . strtoupper($request->query('topic')) . "%'")
                ->whereHas(
                    'conference', function ($query) {
                        $query->whereDate('conf_date', '>=', date("Y-m-d"));
                    }
                );
            return $reports->orderBy('start_time', 'DESC')->get();
        }

        return Report::all();
    }

    public function store(ReportRequest $request)
    {
        $data = $request->validated();

        if ($data['presentation']) {
            $fileName = time() . '_' . $data['presentation']->getClientOriginalName();
            Storage::disk('upload')->putFileAs('', $data['presentation'], $fileName);
            $data['presentation'] = $fileName;
        }

        $report = Report::create($data);

        if ($request->get('online') != 'false') {
            $success = $this->createZoomMeeting($report);

            if ($success) {
                cache()->forget('meetings');
            }

            else {
                return \response(['errors' => ['zoom' => 'An error occurred while creating the zoom meeting, please try again later']], 500);
            }
        }

        return $report->load('user', 'conference', 'comments', 'category', 'meeting');
    }

    public function show(Report $report)
    {
        return $report->load('user', 'conference', 'comments', 'category', 'meeting');
    }

    public function update(Report $report, ReportUpdateRequest $request)
    {
        $data = $request->validated();

        if ($data['presentation']) {
            if ($report->presentation) {
                Storage::disk('upload')->delete($report->presentation);
            }
            $fileName = time() . '_' . $data['presentation']->getClientOriginalName();
            Storage::disk('upload')->putFileAs('', $data['presentation'], $fileName);
            $data['presentation'] = $fileName;
        } else {
            unset($data['presentation']);
        }

        if (!$report->update($data)) {
            return \response(['errors' => ['zoom' => 'An error occurred while updating the zoom meeting, please try again later']], 500);
        }

        if ($request->get('online') != 'false') {
            $success = $this->createZoomMeeting($report, true);

            if ($success) {
                cache()->forget('meetings');
            }

            else {
                return \response(['errors' => ['zoom' => 'An error occurred while creating the zoom meeting, please try again later']], 500);
            }
        }

        return $report->load('user', 'conference', 'comments', 'category', 'meeting');
    }

    public function destroy(Report $report)
    {
        if (!$report->delete()) {
            return \response(['errors' => ['zoom' => 'An error occurred while deleting the zoom meeting, please try again later']], 500);
        }
        else {
            return null;
        }
    }

    public function download(Report $report)
    {
        return Storage::disk('upload')->download($report->presentation);
    }

    public function export(Request $request)
    {
        $reports = $this->getFilteredReports($request)->get();
        ProcessReportsExport::dispatch($reports)->delay(now()->addSeconds(5));;
    }

    public function createZoomMeeting(Report $report, $updReport=false)
    {
        return ZoomMeetingService::store($report, $updReport);
    }
}
