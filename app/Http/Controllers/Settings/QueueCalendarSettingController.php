<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\QueueCalendarSetting;
use App\Models\QueueSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QueueCalendarSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        try {
            $now = Carbon::now();
            $year = $request->query('year', $now->year);
            $queueSetting = QueueSetting::whereUserId($user->id)->with(['queue_calendar_setting' => function ($query) use ($year) {
                $query->whereYear('calendar_date', '=', $year);
                $query->orderBy('calendar_date', 'asc');
            }])->first();

            if (!$queueSetting) {
                return $this->sendBadResponse(null, 'Queue Setting Not Found');
            }

            $queueCalendarSetting = $queueSetting->queue_calendar_setting;
            return $this->sendOkResponse($queueCalendarSetting, 'Calendar Found');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $queueSetting = QueueSetting::whereUserId($user->id)->first();

        if (!$queueSetting) {
            return $this->sendBadResponse(null, 'Queue Setting not found');
        }

        $validator = Validator::make($request->all(), [
            'year' => ['required', 'numeric'],
            'month' => ['required', 'numeric', 'min:1', 'max:12'],
            'business_time_open' => ['required'],
            'business_time_close' => ['required'],
            'day_off' => ['array'],
            // 'allocate_time' => ['required'],
            // 'queue_on_allocate' => ['required'],
            // 'active' => [],
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse($validator->errors(), 'Validation Failed');
        }

        $now = Carbon::now();

        $_year = $request->input('year', 0);
        $_month = $request->input('month', 0);

        try {
            // Create Calendar Date
            $calendar = Carbon::create($_year, $_month);
        } catch (\Throwable $th) {
            return $this->sendBadResponse($th->getMessage(), 'Year & Month incorrect');
        }

        // Check if calendar exists
        $queueCalendarSetting = QueueCalendarSetting::whereCalendarDate($calendar->toDateString())->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        if ($queueCalendarSetting) {
            return $this->update($request, $queueCalendarSetting->id);
        }

        try {
            // Calendar Date is older than Now
            if ($now->year > $calendar->year || ($now->year == $calendar->year && $now->month > $calendar->month)) {
                throw new Exception("Calendar Date is older than Now");
            }

            // Calendar Date is newer more than one month from NOW
            if ($calendar->diffInMonths($now) >= 1) {
                throw new Exception("Calendar Date is newer more than one month from NOW");
            }
        } catch (\Throwable $th) {
            return $this->sendBadResponse($th->getMessage(), $th->getMessage());
        }

        $queueCalendarSetting = new QueueCalendarSetting();
        $queueCalendarSetting->queue_setting_id = $queueSetting->id;
        $queueCalendarSetting->calendar_date = $calendar->toDateString();
        $queueCalendarSetting->business_time_open = $request->input('business_time_open', '09:00:00');
        $queueCalendarSetting->business_time_close = $request->input('business_time_close', '18:00:00');
        $queueCalendarSetting->day_off = $request->input('day_off', []);
        $queueCalendarSetting->allocate_time = $request->input('allocate_time', '00:15:00');
        $queueCalendarSetting->queue_on_allocate = $request->input('queue_on_allocate', 1);

        try {
            $result = $queueCalendarSetting->save();
            return $this->sendOkResponse($queueCalendarSetting, 'Save Calendar Success');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();

        try {
            $queueCalendarSetting = QueueCalendarSetting::whereId($id)->whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();

            return $this->sendOkResponse($queueCalendarSetting, 'Calendar Found');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $queueCalendarSetting = QueueCalendarSetting::whereId($id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        if (!$queueCalendarSetting) {
            return $this->sendBadResponse(null, 'Original data not found');
        }

        $validator = Validator::make($request->all(), [
            'business_time_open' => ['required'],
            'business_time_close' => ['required'],
            'day_off' => ['array'],
            'allocate_time' => ['required'],
            'queue_on_allocate' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse($validator->errors(), 'Validation Failed');
        }

        $now = Carbon::now();
        $calendar = Carbon::parse($queueCalendarSetting->carlendar_date);

        try {
            // Calendar Date is older than Now
            if ($now->year > $calendar->year || ($now->year == $calendar->year && $now->month > $calendar->month)) {
                throw new Exception("Calendar Date is older than Now");
            }

            // Calendar Date is newer more than one month from NOW
            if ($calendar->diffInMonths($now) >= 1) {
                throw new Exception("Calendar Date is newer more than one month from NOW");
            }
        } catch (\Throwable $th) {
            return $this->sendBadResponse($th->getMessage(), $th->getMessage());
        }

        $queueCalendarSetting->business_time_open = $request->input('business_time_open', $queueCalendarSetting->business_time_open);
        $queueCalendarSetting->business_time_close = $request->input('business_time_close', $queueCalendarSetting->business_time_close);
        $queueCalendarSetting->day_off = $request->input('day_off', $queueCalendarSetting->day_off);
        $queueCalendarSetting->allocate_time = $request->input('allocate_time', $queueCalendarSetting->allocate_time);
        $queueCalendarSetting->queue_on_allocate = $request->input('queue_on_allocate', $queueCalendarSetting->queue_on_allocate);

        try {
            $result = $queueCalendarSetting->save();
            return $this->sendOkResponse($queueCalendarSetting, 'Update Calendar Success');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    public function calendarActivate(Request $request, $id)
    {

        $user = Auth::user();

        try {
            $queueCalendarSetting = QueueCalendarSetting::whereId($id)->whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();
        } catch (\Throwable $th) {
            return $this->sendErrorResponse(null, 'DB Error');
        }

        if (!$queueCalendarSetting) {
            return $this->sendBadResponse(null, 'Calendar Not found');
        }

        $queueCalendarSetting->active = 1;
        $result = $queueCalendarSetting->save();

        if ($result) {
            return $this->sendOkResponse(true, 'Calendar is activated');
        }

        return $this->sendBadResponse(null, 'Calendar activate fail');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
