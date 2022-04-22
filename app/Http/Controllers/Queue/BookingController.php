<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Http\Services\LineService;
use App\Models\BookingStatus;
use App\Models\Line\LineMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    private function lineAuthen(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return [false, null, response(['message' => 'Unauthenticated.'], 401)];
        }

        try {
            $lineService = new LineService(['accessToken' => $accessToken]);

            $lineConfig = $lineService->getLineConfig();

            if (!$lineConfig) {
                return [false, null, $this->sendBadResponse(['error' => "LINE_CONFIG_EMPTY"], 'Line Config Not Found')];
            }

            return [true, $lineService, null];
        } catch (\Throwable $th) {
            Log::error('generate_ticket: ' . $th->getMessage());
            return [false, null, response(['message' => 'Unauthenticated.'], 401)];
        }
    }

    function my_booking(Request $request)
    {
        list($status, $lineService, $response) = $this->lineAuthen($request);

        if (!$status) {
            return $response;
        }

        $profile = $lineService->getProfile();

        $lineMember = LineMember::whereUserId($profile['userId'])->first();
    }
}
