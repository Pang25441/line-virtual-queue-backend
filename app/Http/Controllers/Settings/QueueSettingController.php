<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\QueueSetting;
use Illuminate\Http\Request;
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
            'displayName' => 'required|max:255',
            'detail' => 'required|max:255'
        ]);

        $user = $request->user;

        if ($validator->fails()) {
            return $this->sendBadResponse($validator->errors(), 'Validation Failed');
        }

        try {
            $queueSetting = new QueueSetting();
            $queueSetting->user_id = $user->id;
            $queueSetting->display_name = $request->input('displayName');
            $queueSetting->detail = $request->input('detail');
            $queueSetting->save();

            return $this->sendOkResponse($queueSetting, 'Save successful');
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
        $user = $request->user;

        $queueSetting = QueueSetting::where('user_id', $user->id)->first();

        return $this->sendOkResponse($queueSetting);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'displayName' => 'required|max:255',
            'detail' => 'required|max:255'
        ]);

        $user = $request->user;

        if ($validator->fails()) {
            return $this->sendBadResponse($validator->errors(), 'Validation Failed');
        }

        $queueSetting = QueueSetting::where('user_id', $user->id)->first();

        if(!$queueSetting) {
            $queueSetting = new QueueSetting();
        }

        try {
            $queueSetting = new QueueSetting();
            $queueSetting->user_id = $user->id;
            $queueSetting->display_name = $request->input('displayName');
            $queueSetting->detail = $request->input('detail');
            $queueSetting->save();

            return $this->sendOkResponse($queueSetting, 'Update successful');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'Server Error');
        }
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
