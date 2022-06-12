<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
            'email' => ['requerd', 'unique:users,email'],
            'password' => ['required', 'min:4', 'max:100'],
            'password_confirmation' => ['confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        $newUser = new User();
        $newUser->first_name = $request->input("first_name", null);
        $newUser->last_name = $request->input("last_name", null);
        $newUser->name = $newUser->first_name . " " . $newUser->last_name;
        $newUser->email = $request->input("email", null);
        $newUser->password = Hash::make($request->input("password", null));

        try {
            $newUser->save();
        } catch (\Throwable $th) {
            Log::error("Register: " . $th->getMessage());
            return $this->sendBadResponse(false, "Service unavailable");
        }

        return $this->sendOkResponse(true, "Registered");
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {

        $validator = Validator::make($request->all(), [
            'first_name' => ['required'],
            'last_name' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        $user->first_name = $request->input("first_name", null);
        $user->last_name = $request->input("last_name", null);
        $user->name = $user->first_name . " " . $user->last_name;

        try {
            $user->save();
        } catch (\Throwable $th) {
            Log::error("Update user: " . $th->getMessage());
            return $this->sendBadResponse(false, "Service unavailable");
        }

        return $this->sendOkResponse(true, "Saved");
    }

    public function changePassword(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'password_old' => ['required'],
            'password' => ['required'],
            'password_confirmation' => ['confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        $password_old = $request->input("password_old");
        $password = $request->input("password");
        $password_confirmation = $request->input("password_confirmation");

        // Check password
        if(!Hash::check($password_old, $user->password)) {

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
