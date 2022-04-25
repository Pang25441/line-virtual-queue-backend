<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\CalendarSetting;
use App\Models\QueueSetting;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CalendarSettingController extends Controller
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
            $queueSetting = QueueSetting::whereUserId($user->id)->with(['calendar_setting' => function ($query) use ($year) {
                $query->whereYear('calendar_date', '=', $year);
                $query->orderBy('calendar_date', 'asc');
            }])->first();

            if (!$queueSetting) {
                return $this->sendBadResponse(null, 'Queue Setting Not Found');
            }

            $CalendarSetting = $queueSetting->calendar_setting;
            return $this->sendOkResponse($CalendarSetting, 'Calendar Found');
        } catch (\Throwable $th) {
            Log::error("CalendarSettingController: index: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
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
            // 'booking_limit' => ['required'],
            // 'active' => [],
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
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
        $CalendarSetting = CalendarSetting::whereCalendarDate($calendar->toDateString())->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        if ($CalendarSetting) {
            return $this->update($request, $CalendarSetting->id);
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

        $CalendarSetting = new CalendarSetting();
        $CalendarSetting->queue_setting_id = $queueSetting->id;
        $CalendarSetting->calendar_date = $calendar->toDateString();
        $CalendarSetting->business_time_open = $request->input('business_time_open', '09:00:00');
        $CalendarSetting->business_time_close = $request->input('business_time_close', '18:00:00');
        $CalendarSetting->day_off = $request->input('day_off', []);
        $CalendarSetting->allocate_time = $request->input('allocate_time', '00:15:00');
        $CalendarSetting->booking_limit = $request->input('booking_limit', 1);

        try {
            $result = $CalendarSetting->save();
            return $this->sendOkResponse($CalendarSetting, 'Save Calendar Success');
        } catch (\Throwable $th) {
            Log::error("CalendarSettingController: store: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
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
            $CalendarSetting = CalendarSetting::whereId($id)->whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();

            return $this->sendOkResponse($CalendarSetting, 'Calendar Found');
        } catch (\Throwable $th) {
            Log::error("CalendarSettingController: show: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
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

        $CalendarSetting = CalendarSetting::whereId($id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->where('user_id', $user->id);
        })->first();

        if (!$CalendarSetting) {
            return $this->sendBadResponse(null, 'Original data not found');
        }

        $validator = Validator::make($request->all(), [
            'business_time_open' => ['required'],
            'business_time_close' => ['required'],
            'day_off' => ['array'],
            'allocate_time' => ['required'],
            'booking_limit' => ['required'],
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        $now = Carbon::now();
        $calendar = Carbon::parse($CalendarSetting->carlendar_date);

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

        $CalendarSetting->business_time_open = $request->input('business_time_open', $CalendarSetting->business_time_open);
        $CalendarSetting->business_time_close = $request->input('business_time_close', $CalendarSetting->business_time_close);
        $CalendarSetting->day_off = $request->input('day_off', $CalendarSetting->day_off);
        $CalendarSetting->allocate_time = $request->input('allocate_time', $CalendarSetting->allocate_time);
        $CalendarSetting->booking_limit = $request->input('booking_limit', $CalendarSetting->booking_limit);

        try {
            $result = $CalendarSetting->save();
            return $this->sendOkResponse($CalendarSetting, 'Update Calendar Success');
        } catch (\Throwable $th) {
            Log::error("CalendarSettingController: update: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
        }
    }

    public function calendarActivate(Request $request, $id)
    {

        $user = Auth::user();

        try {
            $CalendarSetting = CalendarSetting::whereId($id)->whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->where('user_id', $user->id);
            })->first();
        } catch (\Throwable $th) {
            return $this->sendErrorResponse(null, 'DB Error');
        }

        if (!$CalendarSetting) {
            return $this->sendBadResponse(null, 'Calendar Not found');
        }

        $CalendarSetting->active = 1;
        $result = $CalendarSetting->save();

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
