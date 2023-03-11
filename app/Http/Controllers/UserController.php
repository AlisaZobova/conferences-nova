<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\JoinAnnouncer;
use App\Mail\JoinListener;
use App\Models\Conference;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function update(ProfileUpdateRequest $request)
    {
        $data = $request->validated();
        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        }
        else {
            unset($data['password']);
        }
        $request->user()->update($data);

        return $request->user()->load('roles', 'conferences', 'joinedConferences', 'reports', 'favorites');
    }

    public function join(Conference $conference)
    {
        Auth::user()->joinedConferences()->attach($conference);
        $this->sendEmailJoinUser($conference, Auth::user());
    }

    public function cancel(Conference $conference)
    {
        Auth::user()->joinedConferences()->detach($conference);
    }

    public function addFavorite(Report $report)
    {
        Auth::user()->favorites()->attach($report);
    }

    public function deleteFavorite(Report $report)
    {
        Auth::user()->favorites()->detach($report);
    }

    public function getUser(User $user)
    {
        return $user->load('roles', 'conferences', 'joinedConferences', 'reports', 'favorites');
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
