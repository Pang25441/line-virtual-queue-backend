<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function login(Request $request)
    {
        if (!Auth::attempt($request->only(['email', 'password']))) {
            // $err = ValidationException::withMessages([
            //     'message' => ['The provided credentials are incorrect.'],
            // ]);

            return $this->sendBadResponse(null, 'The provided credentials are incorrect.');
        }

        $request->session()->regenerate();

        return $this->sendOkResponse(null, 'Login Success');
    }

    function logout(Request $request)
    {

        try {

            Auth::logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();
        } catch (\Throwable $th) {

            return $this->sendBadResponse($th->getMessage(), 'Logout Failed');
        }

        return $this->sendOkResponse(null, 'Logout Success');
    }

    function profile(Request $request)
    {
        $user = $request->user();

        unset($user->id);

        return $this->sendOkResponse($user, '');
    }
}
