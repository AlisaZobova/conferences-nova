<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterAdditionalRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Country;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create(
            [
            'password' => Hash::make($data['password']),
            'email' => $data['email'],
            ]
        );

        $user->assignRole($data['type']);

        return $user->load('roles', 'conferences', 'joinedConferences', 'reports', 'favorites');
    }

    public function store_additional(RegisterAdditionalRequest $request, User $user)
    {
        $data = $request->validated();

        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->birthdate =  $data['birthdate'];
        $user->phone = $data['phone'];

        Country::associateCountry($user, $data['country']);

        Auth::login($user);

        return $user->load('roles', 'conferences', 'joinedConferences', 'reports', 'favorites');
    }

}
