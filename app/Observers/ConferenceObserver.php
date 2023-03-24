<?php

namespace App\Observers;

use App\Mail\AdminDeleteConference;
use App\Models\Conference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ConferenceObserver
{
    /**
     * Handle the Conference "deleted" event.
     */
    public function deleted(Conference $conference): void
    {
        if (Auth::user()->hasRole('Admin')) {
            foreach ($conference->users as $joinedUser) {
                Mail::to($joinedUser->email)->send(new AdminDeleteConference($conference));
            }
        }
    }

    public function updated(Conference $conference) {
        if ($conference->conf_date != $conference->getOriginal('conf_date')) {
            foreach ($conference->reports as $report) {
                $report_time = [
                    'start_time' => substr($conference->conf_date, 0, 10) . substr($report->start_time, 10, 16),
                    'end_time' => substr($conference->conf_date, 0, 10) . substr($report->end_time, 10, 16)];
                $report->update($report_time);
            }
        }
    }
}
