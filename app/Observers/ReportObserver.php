<?php

namespace App\Observers;

use App\Mail\AdminDeleteReport;
use App\Mail\AdminUpdateReport;
use App\Mail\JoinAnnouncer;
use App\Mail\JoinListener;
use App\Mail\UpdateReportTime;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use App\Models\ZoomMeeting;
use App\Services\ZoomMeetingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ReportObserver
{

    public function created(Report $report) {
        if (Auth::user() && Auth::user()->hasRole('Admin')) {
            $report->user->joinedConferences()->attach($report->conference);
            $this->sendEmailJoinUser($report->conference, $report->user);
        }
    }

    /**
     * Handle the Report "updated" event.
     */
    public function updated(Report $report): void
    {
        if (Auth::user()->hasRole('Admin')) {

            if ($report->getOriginal('user_id') != $report->user_id ||
                $report->getOriginal('conference_id') != $report->conference_id) {

                $conference = Conference::find($report->getOriginal('conference_id'));
                $user = User::find($report->getOriginal('user_id'));

                $user->joinedConferences()->detach($conference);
                $report->user->joinedConferences()->attach($report->conference);
                $this->sendEmailJoinUser($report->conference, $report->user);
            }

            Mail::to($report->user->email)
                ->send(new AdminUpdateReport($report->conference, $report, $report->getOriginal('topic')));
        }

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
            return ZoomMeetingService::delete($report->meeting->id);
        }
        else {
            return true;
        }
    }

    public function updateZoom($report) {
        $success = ZoomMeetingService::update($report->meeting->id, $report);
        if($success) {
            cache()->forget('meetings');
            $meeting = ZoomMeeting::find($report->meeting->id);
            $meetingFields = [
                'topic' => $report->topic,
                'start_time' => $report->start_time,
            ];
            $meeting->update($meetingFields);
        }
        return $success;
    }

    public function sendEmailJoinUser(Conference $conference, $user) {
        $joinedUsers = $conference->users;

        if($user->hasRole('Announcer')) {
            $report = $conference->reports->where('user_id', $user->id)->first();
            foreach ($joinedUsers as $joinedUser) {
                if($joinedUser->hasRole('Listener')) {
                    Mail::to($joinedUser->email)->send(new JoinAnnouncer($conference, $report, $user));
                }
            }
        }

        else if($user->hasRole('Listener')) {
            foreach ($joinedUsers as $joinedUser) {
                if($joinedUser->hasRole('Announcer')) {
                    Mail::to($joinedUser->email)->send(new JoinListener($conference, $user));
                }
            }
        }
    }
}
