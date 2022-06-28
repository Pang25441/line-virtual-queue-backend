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

    function getTicketGroupByCode(Request $request)
    {
        $ticketGroupCode = $request->input('ticket_group_code', null);

        if (!$ticketGroupCode) {
            return $this->sendBadResponse(['error' => "CODE_EMPTY"], 'Code not found');
        }

        // $lineService = $request->lineService;

        $ticketGroup = TicketGroup::select("id", "ticket_group_code", "active", "active_count", "ticket_group_prefix", "description", "queue_setting_id")->whereTicketGroupCode($ticketGroupCode)->with(["queue_setting" => function ($query) {
            $query->select("id", "display_name", "detail");
        }])->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(['error' => "CODE_REJECT"], 'Ticket Group Not Found');
        }

        if ($ticketGroup->active != 1) {
            return $this->sendBadResponse(['error' => "TICKET_GROUP_INACTIVE"], 'Ticket Group Inactivated');
        }

        $lineService = $request->lineService;

        $lineConfig = $lineService->getLineConfig();

        $profile = $lineService->getProfile();

        $lineMember = LineMember::whereUserId($profile->userId)->whereHas('line_config', function (Builder $query) use ($lineConfig) {
            $query->whereId($lineConfig->id);
        })->first();

        $status = [$this->ticketStatus['PENDING'], $this->ticketStatus['CALLING']];
        $existTicket = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->active_count)->whereLineMemberId($lineMember->id)->whereIn('status', $status)->with(["ticket_group" => function ($query) {
            $query->select("id", "description");
        }])->first();
        $ticketGroup->exist_ticket = $existTicket;

        $waitingCount = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->active_count)->whereStatus($this->ticketStatus['PENDING'])->count();
        $ticketGroup->waiting_count = $waitingCount;

        $lastNumber = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->active_count)->max("count");
        $ticketGroup->next_number = $lastNumber + 1;

        return $this->sendOkResponse($ticketGroup);
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
            return $this->sendBadResponse(['error' => "TICKET_GROUP_INACTIVE"], 'Ticket Group Inactivated');
        }

        $lineConfig = $lineService->getLineConfig();

        $profile = $lineService->getProfile();

        $lineMember = LineMember::whereUserId($profile->userId)->whereHas('line_config', function (Builder $query) use ($lineConfig) {
            $query->whereId($lineConfig->id);
        })->first();

        $status = [$this->ticketStatus['PENDING'], $this->ticketStatus['CALLING']];
        $existsTicket = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->active_count)->whereLineMemberId($lineMember->id)->whereIn('status', $status)->count();

        if ($existsTicket != 0) {
            return $this->sendBadResponse(['error' => "TICKET_EXIST"], 'Ticket already exists');
        }

        $lastTicket = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->active_count)->max('count');

        $ticket = new Ticket();

        try {
            $ticket->ticket_group_id = $ticketGroup->id;
            $ticket->line_member_id = $lineMember->id;
            $ticket->status = 1;
            $ticket->ticket_group_active_count = $ticketGroup->active_count;
            $ticket->count = $lastTicket ? $lastTicket + 1 : 1;
            $ticket->ticket_number = $ticketGroup->ticket_group_prefix . $ticket->count;
            $ticket->save();
        } catch (\Throwable $th) {
            return $this->sendBadResponse(['error' => "TICKET_UNSAVE", 'debug' => $th->getMessage()], 'Cannot create ticket');
        }

        $result = Ticket::select("ticket_group_id", "ticket_number")->whereId($ticket->id)->with(["ticket_group" => function ($query) {
            $query->select("id", "description");
        }])->first();

        // Send Ticket
        try {
            $ticketIsSent = $this->sendTicket($ticket->id);
        } catch (\Throwable $th) {
            $ticketIsSent = false;
        }

        $result->is_sent = $ticketIsSent;

        return $this->sendOkResponse($result, 'Ticket Added');
    }

    private function sendTicket(int $ticketId)
    {
        $ticket = Ticket::whereId($ticketId)->with(["ticket_group.queue_setting", "line_member"])->first();
        $pending_time_object = Carbon::parse($ticket->pending_time);
        $waiting_count = Ticket::whereTicketGroupId($ticket->ticket_group_id)->whereTicketGroupActiveCount($ticket->ticket_group_active_count)->whereStatus($this->ticketStatus['PENDING'])->count();

        $description = $ticket->ticket_group->description;
        $queue_number = $ticket->ticket_number;
        $pending_time = 'Date: ' . $pending_time_object->format("d F Y") . ' Time: ' . $pending_time_object->format("H:i");
        $waiting_queue = 'Waiting Queue: ' . $waiting_count;
        $display_name = $ticket->ticket_group->queue_setting->display_name;

        $ticketTemplateStr = $this->ticketTemplate;

        $ticketTemplateStr = str_replace(
            ['{description}', '{ticket_number}', '{pending_time}', '{waiting_queue}', '{display_name}'],
            [$description, $queue_number, $pending_time, $waiting_queue, $display_name],
            $ticketTemplateStr
        );

        try {
            $ticketTemplate = json_decode($ticketTemplateStr, true);

            $lineService = new LineService(['lineUserId' => $ticket->line_member->user_id]);

            $lineService->sendPushMessage([$ticketTemplate]);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('sendTicket: ' . $th->getMessage());
            return false;
        }

        return true;
    }

    private $ticketTemplate = '
    {
        "type": "flex",
        "altText": "Queue Ticket",
        "contents": {
            "type": "bubble",
            "header": {
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
                    },
                    {
                        "type": "text",
                        "text": "{description}",
                        "size": "14px",
                        "align": "center",
                        "wrap": false,
                        "maxLines": 1,
                        "adjustMode": "shrink-to-fit"
                    }
                ]
            },
            "hero": {
                "type": "box",
                "layout": "vertical",
                "contents": [
                    {
                        "type": "text",
                        "text": "{ticket_number}",
                        "size": "64px",
                        "weight": "bold",
                        "align": "center",
                        "wrap": true,
                        "adjustMode": "shrink-to-fit",
                        "maxLines": 1,
                        "margin": "6px"
                    }
                ]
            },
            "body": {
                "type": "box",
                "layout": "vertical",
                "contents": [
                    {
                        "type": "text",
                        "text": "{pending_time}",
                        "size": "14px",
                        "align": "center"
                    }
                ]
            },
            "footer": {
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
            "size": "kilo",
            "styles": {
                "header": {
                    "backgroundColor": "#f4f6f9"
                },
                "hero": {
                    "backgroundColor": "#f0f2f5"
                },
                "body": {
                    "backgroundColor": "#f4f6f9"
                },
                "footer": {
                    "backgroundColor": "#f4f6f9"
                }
            }
        }
    }';

    public function currentTicket(Request $request)
    {
        $lineService = $request->lineService;

        $profile = $lineService->getProfile();

        $lineMember = LineMember::whereUserId($profile->userId)->first();

        $status = [$this->ticketStatus['PENDING'], $this->ticketStatus['CALLING']];
        $ticket = Ticket::whereLineMemberId($lineMember->id)->whereIn('status', $status)->orderBy('pending_time', 'desc')->first();

        if (!$ticket) {
            return $this->sendBadResponse(['error' => "NO_TICKET"], 'Ticket not found');
        }

        $waiting_count = Ticket::whereTicketGroupId($ticket->ticket_group_id)->whereTicketGroupActiveCount($ticket->ticket_group_active_count)->where('pending_time', '<=', $ticket->pending_time)->whereIn('status', $status)->count();

        $ticket->waiting_count = $waiting_count;

        return $this->sendOkResponse($ticket);
    }
}
