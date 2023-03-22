<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $user = User::where('email', $request->get('email'))->first();

        if ($user && !$user->hasRole('Admin')) {
            $request->authenticate();
            $request->session()->regenerate();
            return Auth::user()->load(
                'roles',
                'conferences:id,user_id',
                'joinedConferences:id,user_id',
                'favorites'
            );

        }

        else {
            throw ValidationException::withMessages(
                [
                    'email' => trans('auth.failed'),
                ]
            );
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }
}
