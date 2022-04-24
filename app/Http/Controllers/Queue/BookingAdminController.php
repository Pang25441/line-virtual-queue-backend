<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\QueueSetting;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $booking->confirmed_by = $user->id;
        $booking->confirmed_date = Carbon::now()->toDateTimeString();

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

        $booking->status = $bookingStatus['REJRECTED'];
        $booking->rejected_by = $user->id;
        $booking->rejected_date = Carbon::now()->toDateTimeString();

        try {
            $booking->save();
            return $this->sendOkResponse($booking, 'Booking Rejected');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse(['error' => 'DB Error'], 'DB Error');
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

        $reviseableStatus = [
            $bookingStatus['PENDING'],
            $bookingStatus['CONFIRMED'],
            $bookingStatus['REVISE'],
        ];

        if ($booking->status != $reviseableStatus['PENDING']) {
            return $this->sendBadResponse(['error' => 'NOT_ALLOW'], 'Booking status already confirmed');
        }

        $booking->status = $bookingStatus['REVISE'];
        $booking->revise_by = $user->id;
        $booking->revise_date = Carbon::now()->toDateTimeString();

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

        $booking->status = $bookingStatus['COMPLETED'];
        $booking->completed_by = $user->id;
        $booking->completed_date = Carbon::now()->toDateTimeString();

        try {
            $booking->save();
            return $this->sendOkResponse($booking, 'Booking Rejected');
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
}
