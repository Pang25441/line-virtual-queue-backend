<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Line\LineMember;
use App\Models\CalendarSetting;
use App\Models\QueueSetting;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    private $bookingStatus = [];

    function __construct()
    {
        $bookingStatus = BookingStatus::all()->mapWithKeys(function ($status) {
            return [$status['code'] => $status['id']];
        });

        $this->bookingStatus = $bookingStatus;
    }

    function my_booking(Request $request)
    {
        $lineService = $request->lineService;

        $profile = $lineService->getProfile();

        Log::debug('my_booking: profile: ' . $profile['display_name']);

        $lineMember = LineMember::whereUserId($profile['userId'])->first();

        $status = [$this->bookingStatus['PENDING'], $this->bookingStatus['CONFIRMED'], $this->bookingStatus['REJECTED'], $this->bookingStatus['REVISE'], $this->bookingStatus['DONE'], $this->bookingStatus['CANCELED']];

        try {
            $booking = Booking::whereLineMemberId($lineMember->id)->whereIn('status', $status)->get();
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }

        return $this->sendOkResponse($booking, 'Success');
    }

    function getHeader(Request $request)
    {
        $lineService = $request->lineService;

        $lineConfig = $lineService->getLineConfig();

        $queueSetting = QueueSetting::whereLineConfigId($lineConfig->id)->first();

        if (!$queueSetting) {
            return $this->sendBadResponse(null, 'Queue Setting Not Found');
        }

        $response = [
            'id' => $queueSetting->id,
            'display_name' => $queueSetting->display_name,
            'detail' => $queueSetting->detail
        ];

        $this->sendOkResponse($response);
    }

    function getCalendarDetail(Request $request, int $year, int $month)
    {
        $lineService = $request->lineService;

        $lineConfig = $lineService->getLineConfig();

        try {
            $findMonth = Carbon::create($year, $month);
        } catch (\Throwable $th) {
            return $this->sendBadResponse(['error' => 'DATE_INCORRECT'], 'Carlendar date incorrect');
        }

        try {
            $queueSetting = QueueSetting::whereLineConfigId($lineConfig->id)->first();
            $calendar = CalendarSetting::whereQueueSettingId($queueSetting->id)->whereCalendarDate($findMonth->toDateTimeString())->first();
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }

        return $this->sendOkResponse($calendar, 'Success');
    }

    function store(Request $request)
    {
        $lineService = $request->lineService;

        $lineConfig = $lineService->getLineConfig();

        $bookingStatus = $this->bookingStatus;

        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'max:150'],
            'customer_contact' => ['required', 'max:100'],
            'booking_date' => ['required', 'date_format:Y-m-d H:i:s']
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(['errors' => $validator->errors()], 'Validation Failed');
        }

        try {
            $bookingDate = Carbon::parse($request->input('booking_date'));
        } catch (\Throwable $th) {
            return $this->sendBadResponse(['error' => 'DATE_INCORRECT'], 'Carlendar date incorrect');
        }

        try {
            $queueSetting = QueueSetting::whereLineConfigId($lineConfig->id)->first();
            $calendar = CalendarSetting::whereQueueSettingId($queueSetting->id)->whereCalendarDate($bookingDate->format('Y-m-d'))->whereRaw($bookingDate->format("H:i:s") . " BETWEEN business_time_open AND business_time_close")->first();
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }

        // If not in business time
        if (!$calendar) {
            return $this->sendBadResponse(['error' => 'NO_BUSINESS'], 'Out of business');
        }

        // Check Available Date
        $is_dayoff = in_array($bookingDate->day, $calendar->day_off);
        if ($is_dayoff) {
            return $this->sendBadResponse(['error' => 'DAYOFF'], 'Unavailable date');
        }

        // Check available slot
        $allocateTime = CarbonInterval::createFromFormat('H:i:s', $calendar->allocate_time);
        $startTime = $bookingDate->format('Y-m-d H:i:s');
        $endTime = $bookingDate->copy()->add($allocateTime)->format('Y-m-d H:i:s');
        $activeBookingStatus = [
            $bookingStatus['PENDING'],
            $bookingStatus['CONFIRMED'],
            $bookingStatus['REVISE'],
        ];
        $currentSlot = Booking::whereCalendarSettingId($calendar->id)->whereBetween('booking_date', [$startTime, $endTime])->whereIn('status', $activeBookingStatus)->get();
        if (count($currentSlot) >= $calendar->booking_limit) {
            return $this->sendBadResponse(['NO_SLOT'], 'Booking slot not available');
        }

        $profile = $lineService->getProfile();

        // Save
        $lineMember = LineMember::whereUserId($profile['userId'])->whereLineConfigId($lineConfig->id)->first();
        $booking = new Booking();
        $booking->calendar_setting_id = $calendar->id;
        $booking->line_member_id = $lineMember->id;
        $booking->status = $bookingStatus['PENDING'];
        $booking->booking_code = uniqid($calendar->queue_setting_id . $calendar->id . $lineMember->id);
        $booking->customer_name = $request->input('customer_name');
        $booking->customer_contact = $request->input('customer_contact');
        $booking->booking_date = $request->input('booking_date');

        try {
            $result = $booking->save();
            return $this->sendOkResponse($result, 'Booking Successful');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    // function update(Request $request, $bookingId)
    // {
    // }

    function show(Request $request, int $bookingId)
    {
        $lineService = $request->lineService;

        // $lineConfig = $lineService->getLineConfig();

        $profile = $lineService->getProfile();

        $lineMember = LineMember::whereUserId($profile['userId'])->first();

        $booking = Booking::whereId($bookingId)->whereLineMemberId($lineMember->id);

        if(!$booking) {
            return $this->sendBadResponse(null, 'Booking not found');
        }

        return $this->sendOkResponse($booking, 'Booing found');
    }

    function bookingCancel(Request $request, int $bookingId)
    {
        $lineService = $request->lineService;

        $bookingStatus = $this->bookingStatus;

        $lineMember = $lineService->getProfile();

        $activeBookingStatus = [
            $bookingStatus['PENDING'],
            $bookingStatus['CONFIRMED'],
            $bookingStatus['REVISE'],
        ];

        $booking = Booking::whereId($bookingId)->whereLineMemberId($lineMember->id)->whereIn('status', $activeBookingStatus)->first();

        if(!$booking) {
            return $this->sendBadResponse(null, 'Booking not found');
        }

        $booking->status = $bookingStatus['CANCELED'];

        try {
            $booking->save();
            return $this->sendOkResponse(true,'Booking Canceled');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }
}
