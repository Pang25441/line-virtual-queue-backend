<?php

namespace App\Http\Controllers\Line;

use App\Http\Controllers\Controller;
use App\Models\Line\LineConfig;
use App\Models\Line\LineLiffConfig;
use Illuminate\Http\Request;

class LineConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $data = LineConfig::all();
        // return $this->sendOkResponse($data);
        return response('',404);
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
     * @param  \App\Models\Line\LineConfig  $lineConfig
     * @return \Illuminate\Http\Response
     */
    public function show(LineConfig $lineConfig)
    {
        $data = $lineConfig;
        return $this->sendOkResponse($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Line\LineConfig  $lineConfig
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LineConfig $lineConfig)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Line\LineConfig  $lineConfig
     * @return \Illuminate\Http\Response
     */
    public function destroy(LineConfig $lineConfig)
    {
        //
    }
}
