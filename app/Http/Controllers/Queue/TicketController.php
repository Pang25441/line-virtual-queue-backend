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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LINE\LINEBot;

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

    public function callNextQueue(Request $request, int $ticketGroupId)
    {

        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $ticketGroupId)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $calling_count = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->active_count)->whereStatus($this->ticketStatus['CALLING'])->whereIsPostpone(0)->count();
        $waiting_queue = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->active_count)->whereStatus($this->ticketStatus['PENDING'])->orderBy('pending_time', 'asc')->with('line_member')->first();

        if ($calling_count > 0) {
            return $this->sendBadResponse($waiting_queue, 'Queue slot not empty');
        }

        $now = Carbon::now();
        $waiting_queue->status = $this->ticketStatus['CALLING'];
        $waiting_queue->calling_time = $now->toDateTimeString();

        try {
            $waiting_queue->save();
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'Cannot Update Ticket Status');
        }

        // Send Queue Notify
        $messageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder("Your Queue Is Ready");
        $message = $messageBuilder->buildMessage();

        try {
            $lineService = new LineService(['lineUserId' => $waiting_queue->line_member->user_id]);
            $lineService->sendPushMessage($message);
        } catch (\Throwable $th) {
            Log::error('callNextQueue: ' . $th->getMessage());
        }

        return $this->sendOkResponse($waiting_queue);
    }

    public function recallQueue(Request $request, int $ticketId)
    {
        $user = Auth::user();

        $ticket = Ticket::whereId($ticketId)->with(['ticket_group', 'ticket_group.queue_setting', 'line_member']);

        if ($ticket->ticket_group->queue_setting->user_id != $user->id) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        if ($ticket->status != $this->ticketStatus['CALLING']) {
            return $this->sendBadResponse(null, 'Queue Cannot Recall');
        }

        // Send Queue Notify
        $messageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder("Your Queue Is Ready (Recall)");
        $message = $messageBuilder->buildMessage();
        try {
            $lineService = new LineService(['lineUserId' => $ticket->line_member->user_id]);
            $lineService->sendPushMessage($message);
            return $this->sendOkResponse($ticket, 'Recall Success');
        } catch (\Throwable $th) {
            Log::error('recallQueue: ' . $th->getMessage());
            return $this->sendErrorResponse(null, 'Recall failed');
        }
    }

    public function executeQueue(Request $request, int $ticketId)
    {
        $user = Auth::user();

        $ticket = Ticket::whereId($ticketId)->with(['ticket_group', 'ticket_group.queue_setting']);

        if ($ticket->ticket_group->queue_setting->user_id != $user->id) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $ticket->status = $this->ticketStatus['EXECUTED'];

        try {
            $ticket->save();
            return $this->sendOkResponse($ticket, 'Queue Executed');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    public function postponeQueue(Request $request, int $ticketId)
    {
        $user = Auth::user();

        $ticket = Ticket::whereId($ticketId)->with(['ticket_group', 'ticket_group.queue_setting']);

        if ($ticket->ticket_group->queue_setting->user_id != $user->id) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $ticket->is_postpone = 1;

        try {
            $ticket->save();
            return $this->sendOkResponse($ticket, 'Queue Postpone');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    public function rejectQueue(Request $request, int $ticketId)
    {
        $user = Auth::user();

        $ticket = Ticket::whereId($ticketId)->with(['ticket_group', 'ticket_group.queue_setting']);

        if ($ticket->ticket_group->queue_setting->user_id != $user->id) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $ticket->status = $this->ticketStatus['REJECTED'];

        try {
            $ticket->save();
            return $this->sendOkResponse($ticket, 'Queue Rejected');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    public function currentTicket(Request $request)
    {
        $accessToken = $request->bearerToken();

        if (!$accessToken) {
            return response(['message' => 'Unauthenticated.'], 401);
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
