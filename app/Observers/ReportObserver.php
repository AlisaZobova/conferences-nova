<?php

namespace App\Observers;

use App\Http\Controllers\ZoomMeetingController;
use App\Mail\AdminDeleteReport;
use App\Mail\UpdateReportTime;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ReportObserver
{

    /**
     * Handle the Report "updated" event.
     */
    public function updated(Report $report): void
    {
        if ($report->start_time != $report->getOriginal('start_time') ||
            $report->end_time != $report->getOriginal('end_time')) {

            $conference = $report->conference;
            $joinedUsers = $conference->users;

            foreach ($joinedUsers as $joinedUser) {
                if ($joinedUser->hasRole('Listener')) {
                    Mail::to($joinedUser->email)->send(new UpdateReportTime($conference, $report, $report->user));
                }
            }
        }
    }

    public function updating(Report $report) {

        if (($report->start_time != $report->getOriginal('start_time') ||
            $report->end_time != $report->getOriginal('end_time') ||
            $report->topic != $report->getOriginal('topic'))
            && $report->meeting) {

            return $this->updateZoom($report);
        }
        else {
            return true;
        }
    }

    /**
     * Handle the Report "deleted" event.
     */
    public function deleted(Report $report): void
    {
        if (Auth::user()->hasRole('Admin')) {
            $report->user->joinedConferences()->detach($report->conference);
            Mail::to($report->user->email)->send(new AdminDeleteReport($report->conference));
        }

        if ($report->meeting) {
            $report->meeting()->forceDelete();
            cache()->forget('meetings');
        }
    }

    public function deleting(Report $report) {
        if ($report->meeting) {
            $zoom = new ZoomMeetingController();
            return $zoom->delete($report->meeting->id);
        }
        else {
            return true;
        }
    }

    public function updateZoom($report) {
        $zoom = new ZoomMeetingController();
        $success = $zoom->update($report->meeting->id, $report);
        if($success) {
            cache()->forget('meetings');
        }
        return $success;
    }
}
