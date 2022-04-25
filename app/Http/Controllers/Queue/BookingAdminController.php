<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\CalendarSetting;
use App\Models\QueueSetting;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingAdminController extends Controller
{
    private $bookingStatus = [];

    function __construct()
    {
        $bookingStatus = BookingStatus::all()->mapWithKeys(function ($status) {
            return [$status['code'] => $status['id']];
        });

        $this->bookingStatus = $bookingStatus;
    }

    function show(Request $request, int $bookingId)
    {
        $user = Auth::user();

        $booking = Booking::whereId($bookingId)->whereHas('calendar_setting.queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$booking) {
            return $this->sendBadResponse(['error' => 'NOT_FOUND'], 'Booking Not Found');
        }

        return $this->sendOkResponse($booking, 'Founded');
    }

    function confirmBooking(Request $request, int $bookingId)
    {
        $user = Auth::user();

        $bookingStatus = $this->bookingStatus;

        $booking = Booking::whereId($bookingId)->whereHas('calendar_setting.queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$booking) {
            return $this->sendBadResponse(['error' => 'NOT_FOUND'], 'Booking Not Found');
        }

        if ($booking->status != $bookingStatus['PENDING']) {
            return $this->sendBadResponse(['error' => 'NOT_ALLOW'], 'Booking status already confirmed');
        }

        $booking->status = $bookingStatus['CONFIRMED'];
        $booking->confirm_by = $user->id;
        $booking->confirm_date = Carbon::now()->toDateTimeString();

        try {
            $booking->save();
            return $this->sendOkResponse($booking, 'Booking confirmed');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse(['error' => 'DB Error'], 'DB Error');
        }
    }

    function rejectBooking(Request $request, int $bookingId)
    {
        $user = Auth::user();

        $bookingStatus = $this->bookingStatus;

        $booking = Booking::whereId($bookingId)->whereHas('calendar_setting.queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$booking) {
            return $this->sendBadResponse(['error' => 'NOT_FOUND'], 'Booking Not Found');
        }

        $rejectableStatus = [
            $bookingStatus['PENDING'],
            $bookingStatus['CONFIRMED'],
            $bookingStatus['REVISE'],
        ];

        if (!in_array($booking->status, $rejectableStatus)) {
            return $this->sendBadResponse(['error' => 'NOT_ALLOW'], 'Booking status already rejected or completed');
        }

        $booking->status = $bookingStatus['REJECTED'];
        $booking->reject_by = $user->id;
        $booking->reject_date = Carbon::now()->toDateTimeString();

        try {
            $booking->save();
            return $this->sendOkResponse($booking, 'Booking Rejected');
        } catch (\Throwable $th) {
            Log::error('rejectBooking: ' . $th->getMessage());
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    function reviseBooking(Request $request, int $bookingId)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => ['required', 'max:150'],
            'customer_contact' => ['required', 'max:100'],
            'booking_date' => ['required', 'date_format:Y-m-d H:i:s']
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(['errors' => $validator->errors()], 'Validation Failed');
        }

        $user = Auth::user();

        $bookingStatus = $this->bookingStatus;

        $booking = Booking::whereId($bookingId)->whereHas('calendar_setting.queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$booking) {
            return $this->sendBadResponse(['error' => 'NOT_FOUND'], 'Booking Not Found');
        }

        // Check Calendar
        try {
            $bookingDate = Carbon::parse($request->input('booking_date'));
        } catch (\Throwable $th) {
            return $this->sendBadResponse(['error' => 'DATE_INCORRECT'], 'Carlendar date incorrect');
        }

        try {
            $queueSetting = QueueSetting::whereUserId($user->id)->first();
            $calendar = CalendarSetting::whereQueueSettingId($queueSetting->id)->whereCalendarDate($bookingDate->format('Y-m-01'))->whereActive(1)->whereRaw("'" . $bookingDate->format('H:i:s') . "'" . " BETWEEN business_time_open AND business_time_close")->first();
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

        // Check Status
        $reviseableStatus = [
            $bookingStatus['PENDING'],
            $bookingStatus['CONFIRMED'],
            $bookingStatus['REVISE'],
        ];

        if (!in_array($booking->status, $reviseableStatus)) {
            return $this->sendBadResponse(['error' => 'NOT_ALLOW'], 'Booking status already confirmed');
        }

        // Save
        $booking->status = $bookingStatus['REVISE'];
        $booking->revise_by = $user->id;
        $booking->revise_date = Carbon::now()->toDateTimeString();
        $booking->booking_date = $request->input('booking_date');
        $booking->customer_name = $request->input('customer_name');
        $booking->customer_contact = $request->input('customer_contact');

        try {
            $booking->save();
            return $this->sendOkResponse($booking, 'Booking revise and confirmed');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse(['error' => 'DB Error'], 'DB Error');
        }
    }

    function completeBooking(Request $request, int $bookingId)
    {
        $user = Auth::user();

        $bookingStatus = $this->bookingStatus;

        $booking = Booking::whereId($bookingId)->whereHas('calendar_setting.queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$booking) {
            return $this->sendBadResponse(['error' => 'NOT_FOUND'], 'Booking Not Found');
        }

        $completeableStatus = [
            $bookingStatus['CONFIRMED'],
            $bookingStatus['REVISE'],
        ];

        if (!in_array($booking->status, $completeableStatus)) {
            return $this->sendBadResponse(['error' => 'NOT_ALLOW'], 'Booking status already completed');
        }

        $booking->status = $bookingStatus['COMPLETE'];
        $booking->complete_by = $user->id;
        $booking->complete_date = Carbon::now()->toDateTimeString();

        try {
            $booking->save();
            return $this->sendOkResponse($booking, 'Booking Completed');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse(['error' => 'DB Error'], 'DB Error');
        }
    }

    function getBookingByCode(Request $request)
    {
        $user = Auth::user();

        $bookingCode = $request->input('booking_code', null);

        $booking = Booking::whereBookingCode($bookingCode)->whereHas('calendar_setting.queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$booking) {
            return $this->sendBadResponse(['error' => 'NOT_FOUND'], 'Booking Not Found');
        }

        return $this->sendOkResponse($booking, 'Found');
    }

    private $bookingTemplate = '
    {
        "type": "bubble",
        "body": {
          "type": "box",
          "layout": "vertical",
          "spacing": "md",
          "contents": [
            {
              "type": "text",
              "text": "{display_name}",
              "wrap": true,
              "weight": "bold",
              "gravity": "center",
              "size": "xl"
            },
            {
              "type": "text",
              "text": "{detail}"
            },
            {
              "type": "box",
              "layout": "vertical",
              "margin": "lg",
              "spacing": "sm",
              "contents": [
                {
                  "type": "box",
                  "layout": "baseline",
                  "spacing": "sm",
                  "contents": [
                    {
                      "type": "text",
                      "text": "Name",
                      "color": "#aaaaaa",
                      "size": "sm",
                      "flex": 1
                    },
                    {
                      "type": "text",
                      "text": "{customer_name}",
                      "wrap": true,
                      "color": "#666666",
                      "size": "sm",
                      "flex": 4
                    }
                  ]
                },
                {
                  "type": "box",
                  "layout": "baseline",
                  "spacing": "sm",
                  "contents": [
                    {
                      "type": "text",
                      "text": "Date",
                      "color": "#aaaaaa",
                      "size": "sm",
                      "flex": 1
                    },
                    {
                      "type": "text",
                      "text": "{booking_date}",
                      "wrap": true,
                      "size": "sm",
                      "color": "#666666",
                      "flex": 4
                    }
                  ]
                },
                {
                  "type": "box",
                  "layout": "baseline",
                  "spacing": "sm",
                  "contents": [
                    {
                      "type": "text",
                      "text": "Time",
                      "color": "#aaaaaa",
                      "size": "sm",
                      "flex": 1
                    },
                    {
                      "type": "text",
                      "text": "{booking_time}",
                      "wrap": true,
                      "color": "#666666",
                      "size": "sm",
                      "flex": 4
                    }
                  ]
                }
              ]
            },
            {
              "type": "box",
              "layout": "vertical",
              "margin": "xxl",
              "contents": [
                {
                  "type": "image",
                  "url": "{qrcode_url}",
                  "aspectMode": "cover",
                  "size": "xl",
                  "margin": "md"
                },
                {
                  "type": "text",
                  "text": "Use this code to confirm booking at the place",
                  "color": "#aaaaaa",
                  "wrap": true,
                  "margin": "xxl",
                  "size": "xs"
                }
              ]
            }
          ]
        }
      }';

    private function sendBooking(Booking $booking)
    {
        $needle = ['display_name', 'detail', 'customer_name', 'booking_date', 'booking_time', 'qrcode_url'];
    }
}
