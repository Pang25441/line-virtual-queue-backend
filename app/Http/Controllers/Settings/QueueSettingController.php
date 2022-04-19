<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\QueueSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QueueSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
            'display_name' => 'required|max:255',
            'detail' => 'required|max:255'
        ]);

        $user = Auth::user();

        if ($validator->fails()) {
            return $this->sendBadResponse(['errors' => $validator->errors()], 'Validation Failed');
        }

        $queueSetting = QueueSetting::whereUserId($user->id)->first();

        if (!$queueSetting) {
            $queueSetting = new QueueSetting();
        }

        try {
            $queueSetting->user_id = $user->id;
            $queueSetting->display_name = $request->input('display_name');
            $queueSetting->detail = $request->input('detail');
            $queueSetting->save();

            return $this->sendOkResponse($queueSetting, 'Update successful');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'Server Error');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = Auth::user();

        try {
            $queueSetting = QueueSetting::where('user_id', $user->id)->first();
            return $this->sendOkResponse($queueSetting, $queueSetting ? 'Setting found' : 'Setting not found');
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        return $this->store($request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // No Destroy
    }
}
