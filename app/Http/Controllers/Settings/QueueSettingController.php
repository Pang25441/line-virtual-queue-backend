<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\QueueSetting;
use Illuminate\Http\Request;

class QueueSettingController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\QueueSetting  $queueSetting
     * @return \Illuminate\Http\Response
     */
    public function show(QueueSetting $queueSetting)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\QueueSetting  $queueSetting
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, QueueSetting $queueSetting)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\QueueSetting  $queueSetting
     * @return \Illuminate\Http\Response
     */
    public function destroy(QueueSetting $queueSetting)
    {
        // No Destroy
    }
}
