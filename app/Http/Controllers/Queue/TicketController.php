<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Http\Services\LineService;
use App\Models\Line\LineMember;
use App\Models\Ticket;
use App\Models\TicketGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{

    // function test(Request $request)
    // {
    //     $userId = $request->input('userId');

    //     try {
    //         $lineService = new LineService(['lineUserId' => $userId]);
    //     } catch (\Throwable $th) {
    //         Log::error('TicketController: ' . $th->getMessage());
    //         return response(['message' => 'Unauthenticated.'], 401);
    //     }

    //     $profile = $lineService->getProfile($userId);

    //     return $this->sendOkResponse($profile);
    // }

    function generate_ticket(Request $request)
    {
        $accessToken = $request->bearerToken();

        $ticketGroupCode = $request->input('ticket_group_code', null);

        if (!$accessToken) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        if (!$ticketGroupCode) {
            return $this->sendBadResponse(['error' => "CODE_EMPTY"], 'Code not found');
        }

        $ticketGroup = TicketGroup::whereTicketGroupCode($ticketGroupCode)->first();
        if (!$ticketGroup) {
            return $this->sendBadResponse(['error' => "CODE_REJECT"], 'Ticket Group Not Found');
        }

        if ($ticketGroup->active != 1) {
            return $this->sendBadResponse(['error' => "TICKET_INACTIVE"], 'Ticket Group Inactivated');
        }

        try {
            $lineService = new LineService(['accessToken' => $accessToken]);
        } catch (\Throwable $th) {
            Log::error('generate_ticket: ' . $th->getMessage());
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $lineConfig = $lineService->getLineConfig();

        if (!$lineConfig) {
            return $this->sendBadResponse(['error' => "LINE_CONFIG_EMPTY"], 'Line Config Not Found');
        }

        $profile = $lineService->getProfile($accessToken);

        $lineMember = LineMember::whereUserId($profile['userId'])->whereHas('line_config', function (Builder $query) use ($lineConfig) {
            $query->whereId($lineConfig->id);
        })->first();

        $lastTicket = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->ticket_group_active_count)->max('count');

        $ticket = new Ticket();

        try {
            $ticket->ticket_group_id = $ticketGroup->id;
            $ticket->line_member_id = $lineMember->id;
            $ticket->status = 1;
            $ticket->ticket_group_active_count = $ticketGroup->ticket_group_active_count;
            $ticket->count = $lastTicket ? $lastTicket + 1 : 1;
            $ticket->ticket_number = $ticketGroup->ticket_group_prefix . $ticket->count;
            $ticket->save();
        } catch (\Throwable $th) {
            return $this->sendBadResponse(['error' => "TICKET_UNSAVE", 'debug' => $th->getMessage()], 'Cannot create ticket');
        }

        // Send Ticket

        return $this->sendOkResponse($ticket, 'Ticket Added');
    }

    private function sendTicket(Ticket $ticket) {

    }
}
