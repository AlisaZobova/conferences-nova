<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class NovaLoginController extends Controller
{

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->get('email'))->first();

        if ($user && $user->hasRole('Admin')) {
            $request->authenticate();
            $request->session()->regenerate();
            return Auth::user();

        }

        else {
            return abort(403);
        }
    }
}
