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
}
