<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Http\Services\LineService;
use App\Models\Line\LineMember;
use App\Models\Ticket;
use App\Models\TicketGroup;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{

    private $ticketStatus = [];

    function __construct()
    {
        $ticketStatus = TicketStatus::all()->mapWithKeys(function ($status) {
            return [$status['code'] => $status['id']];
        });

        $this->ticketStatus = $ticketStatus;
    }

    function generateTicket(Request $request)
    {

        $ticketGroupCode = $request->input('ticket_group_code', null);

        if (!$ticketGroupCode) {
            return $this->sendBadResponse(['error' => "CODE_EMPTY"], 'Code not found');
        }

        $lineService = $request->lineService;

        $ticketGroup = TicketGroup::whereTicketGroupCode($ticketGroupCode)->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(['error' => "CODE_REJECT"], 'Ticket Group Not Found');
        }

        if ($ticketGroup->active != 1) {
            return $this->sendBadResponse(['error' => "TICKET_INACTIVE"], 'Ticket Group Inactivated');
        }

        $lineConfig = $lineService->getLineConfig();

        $profile = $lineService->getProfile();

        $lineMember = LineMember::whereUserId($profile['userId'])->whereHas('line_config', function (Builder $query) use ($lineConfig) {
            $query->whereId($lineConfig->id);
        })->first();

        $status = [$this->ticketStatus['PENDING'], $this->ticketStatus['PENDING']];
        $existsTicket = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->ticket_group_active_count)->whereLineMemberId($lineMember->id)->whereIn('status', $status)->count();

        if($existsTicket != 0) {
            return $this->sendBadResponse(['error' => "TICKET_EXISTS"], 'Ticket already exists');
        }

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
        $this->sendTicket($ticket);

        return $this->sendOkResponse($ticket, 'Ticket Added');
    }

    private function sendTicket(Ticket $ticket)
    {
        $pending_time_object = Carbon::parse($ticket->pending_time);
        $waiting_count = Ticket::whereTicketGroupId($ticket->ticket_group_id)->whereTicketGroupActiveCount($ticket->ticket_group_active_count)->count();

        $description = $ticket->ticket_group()->description;
        $queue_number = $ticket->ticket_number;
        $pending_time = 'Date: ' . $pending_time_object->format("d F Y") . ' Time: ' . $pending_time_object->format("H:i");
        $waiting_queue = 'Waiting Queue: ' . $waiting_count;
        $display_name = $ticket->ticket_group()->queue_setting()->display_name;

        $ticketTemplateStr = $this->ticketTemplate;

        $ticketTemplateStr = str_replace(
            ['{description}', '{ticket_number}', '{pending_time}', '{waiting_queue}', '{display_name}'],
            [$description, $queue_number, $pending_time, $waiting_queue, $display_name],
            $ticketTemplateStr
        );

        try {
            $ticketTemplate = json_decode($ticketTemplateStr);

            $lineService = new LineService(['lineUserId' => $ticket->line_member()->user_id]);

            $lineService->sendPushMessage($ticketTemplate);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('sendTicket: ' . $th->getMessage());
        }

    }

    private $ticketTemplate = '
    {
        "type": "bubble",
        "header": {
          "type": "box",
          "layout": "vertical",
          "contents": [
            {
              "type": "text",
              "text": "{description}",
              "size": "12px",
              "align": "center",
              "wrap": false,
              "maxLines": 1,
              "adjustMode": "shrink-to-fit"
            },
            {
              "type": "text",
              "text": "{ticket_number}",
              "size": "24px",
              "weight": "bold",
              "align": "center",
              "wrap": true,
              "adjustMode": "shrink-to-fit",
              "maxLines": 1,
              "margin": "6px"
            },
            {
              "type": "text",
              "text": "{pending_time}",
              "size": "14px",
              "align": "center",
              "margin": "10px"
            }
          ]
        },
        "hero": {
          "type": "box",
          "layout": "vertical",
          "contents": [
            {
              "type": "text",
              "text": "{waiting_queue}",
              "size": "14px",
              "align": "center"
            }
          ]
        },
        "body": {
          "type": "box",
          "layout": "vertical",
          "contents": [
            {
              "type": "text",
              "text": "{display_name}",
              "weight": "bold",
              "size": "18px",
              "align": "center",
              "wrap": true,
              "adjustMode": "shrink-to-fit",
              "maxLines": 2
            }
          ]
        }
    }';

    public function currentTicket(Request $request)
    {
        $lineService = $request->lineService;

        $profile = $lineService->getProfile();

        $lineMember = LineMember::whereUserId($profile['userId'])->first();

        $status = [$this->ticketStatus['PENDING'], $this->ticketStatus['CALLING']];
        $ticket = Ticket::whereLineMemberId($lineMember->id)->whereIn('status', $status)->orderBy('pending_time', 'desc')->first();

        if (!$ticket) {
            return $this->sendBadResponse(null, 'Ticket not found');
        }

        $waiting_count = Ticket::whereTicketGroupId($ticket->ticket_group_id)->whereTicketGroupActiveCount($ticket->ticket_group_active_count)->where('pending_time', '<=', $ticket->pending_time)->whereIn('status', $status)->count();

        $ticket->waiting_count = $waiting_count;

        return $this->sendOkResponse($ticket);
    }
}
