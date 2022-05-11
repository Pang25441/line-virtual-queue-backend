<?php

namespace App\Http\Controllers\Queue;

use App\Http\Controllers\Controller;
use App\Http\Services\LineService;
use App\Models\Ticket;
use App\Models\TicketGroup;
use App\Models\TicketStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use LINE\LINEBot;

use function PHPSTORM_META\map;

class TicketAdminController extends Controller
{
    private $ticketStatus = [];

    function __construct()
    {
        $ticketStatus = TicketStatus::all()->mapWithKeys(function ($status) {
            return [$status['code'] => $status['id']];
        });

        $this->ticketStatus = $ticketStatus;
    }

    public function getQueueGroup(Request $request)
    {
        $user = Auth::user();

        $ticketGroups = TicketGroup::with(['tickets.line_member'])
            ->whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->whereUserId($user->id);
            })
            ->get()->toArray();

        $ticketGroups = collect($ticketGroups)->map(function ($ticket_group) {
            $ticket_group['tickets'] = collect($ticket_group['tickets'])->reject(function ($ticket) use ($ticket_group) {
                return ($ticket['ticket_group_active_count'] != $ticket_group['active_count']);
            });
            return $ticket_group;
        });

        if (!$ticketGroups) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        return $this->sendOkResponse($ticketGroups, 'Ticket Group');
    }

    public function getTicketStatus()
    {
        $ticketStatus = TicketStatus::all();
        return $this->sendOkResponse($ticketStatus, 'Ticket Status');
    }

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
        $messageBuilder = new LINEBot\MessageBuilder\TextMessageBuilder($waiting_queue->ticket_number . " Your Queue Is Ready");
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

        $ticket = Ticket::whereId($ticketId)->with(['ticket_group', 'ticket_group.queue_setting', 'line_member'])->first();

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

        $ticket = Ticket::whereId($ticketId)->with(['ticket_group', 'ticket_group.queue_setting', 'line_member'])->first();

        if ($ticket->ticket_group->queue_setting->user_id != $user->id) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $ticket->status = $this->ticketStatus['EXECUTED'];

        try {
            $ticket->save();
            return $this->sendOkResponse($ticket, 'Queue Executed');
        } catch (\Throwable $th) {
            Log::error("executeQueue: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
        }
    }

    public function postponeQueue(Request $request, int $ticketId)
    {
        $user = Auth::user();

        $ticket = Ticket::whereId($ticketId)->with(['ticket_group', 'ticket_group.queue_setting', 'line_member'])->first();

        if ($ticket->ticket_group->queue_setting->user_id != $user->id) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $now = Carbon::now();
        $ticket->is_postpone = 1;
        $ticket->postpone_time = $now->toDateTimeString();

        try {
            $ticket->save();
            return $this->sendOkResponse($ticket, 'Queue Postpone');
        } catch (\Throwable $th) {
            Log::error("postponeQueue: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
        }
    }

    public function rejectQueue(Request $request, int $ticketId)
    {
        $user = Auth::user();

        $ticket = Ticket::whereId($ticketId)->with(['ticket_group', 'ticket_group.queue_setting', 'line_member'])->first();

        if ($ticket->ticket_group->queue_setting->user_id != $user->id) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $ticket->status = $this->ticketStatus['REJECTED'];

        try {
            $ticket->save();
            return $this->sendOkResponse($ticket, 'Queue Rejected');
        } catch (\Throwable $th) {
            Log::error("rejectQueue: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
        }
    }

    public function getAllQueue(Request $request, int $ticketGroupId)
    {
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $ticketGroupId)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        try {
            $tickets = Ticket::whereTicketGroupId($ticketGroup->id)->whereTicketGroupActiveCount($ticketGroup->ticket_group_active_count)->orderBy('pending_time', 'asc')->get();
            return $this->sendOkResponse($tickets);
        } catch (\Throwable $th) {
            Log::error("getAllQueue: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
        }
    }

    public function getWaitingQueue(Request $request, int $ticketGroupId)
    {

        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $ticketGroupId)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return response(['message' => 'Unauthenticated.'], 401);
        }

        $status = [$this->ticketStatus['PENDING'], $this->ticketStatus['CALLING']];

        try {
            $tickets = Ticket::whereTicketGroupId($ticketGroup->id)
                ->whereTicketGroupActiveCount($ticketGroup->ticket_group_active_count)
                ->whereIn('status', $status)
                ->orderBy('pending_time', 'asc')
                ->get();
            return $this->sendOkResponse($tickets);
        } catch (\Throwable $th) {
            Log::error("getWaitingQueue: " . $th->getMessage());
            return $this->sendErrorResponse(['error' => 'DB_ERROR'], 'DB Error');
        }
    }
}
