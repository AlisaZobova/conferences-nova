<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\JoinAnnouncer;
use App\Mail\JoinListener;
use App\Models\Conference;
use App\Models\Plan;
use App\Models\Report;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
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

    public function subscribe(Request $request) {

        try {
            $subscriptions = Auth::user()->subscriptions()->where('stripe_status', 'active')->get();
            foreach ($subscriptions as $subscription) {
                $subscription->cancel();
            }
            Auth::user()
                ->newSubscription($request['plan']['name'], $request['plan']['stripe_plan'])
                ->create($request['paymentMethodId']);
            return response('Success', 200);

        } catch (Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }
    }
    public function unsubscribe(Request $request) {

        try {
            $subscriptions = Auth::user()->subscriptions()->where('stripe_status', 'active')->get();
            foreach ($subscriptions as $subscription) {
                $subscription->cancel();
            }
            Auth::user()
                ->newSubscription('Free', 'price_1MncnEDyniFMFJ6WGZNAwRff')
                ->create();
            return response('Success', 200);

        } catch (Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function join(Conference $conference)
    {
        $plan = Plan::where('name', Auth::user()->subscriptions[0]->name)->first();
        if ($plan->joins_per_month && count(Auth::user()->joinedConferences) >= $plan->joins_per_month) {
            return response(['errors' => ['plan' => 'The available monthly joins for the current plan have run out!']], 500);
        } else {
            Auth::user()->joinedConferences()->attach($conference);
            $this->sendEmailJoinUser($conference, Auth::user());
            return response('', 200);
        }
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
