<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\QueueSetting;
use App\Models\TicketGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TicketGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();

        try {
            $ticketGroup = TicketGroup::whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->whereUserId($user->id);
            })->orderBy('ticket_group_prefix', 'ASC')->get();
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }

        return $this->sendOkResponse($ticketGroup);
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
            'ticket_group_prefix' => ['required', 'string'],
            'description' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        // Check if duplicate
        $ticketGroup = TicketGroup::whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })
            ->where('ticket_group_prefix', $request->input('ticket_group_prefix', ''))
            ->get();

        if (count($ticketGroup) >= 1) {
            $validator = Validator::make($request->all(), [
                'ticket_group_prefix' => ['unique:ticket_group,ticket_group_prefix']
            ]);
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        $ticketGroup = new TicketGroup();
        $ticketGroup->queue_setting_id = $queueSetting->id;
        $ticketGroup->unique_key = uniqid();
        $ticketGroup->ticket_group_prefix = $request->input('ticket_group_prefix', '');
        $ticketGroup->description = $request->input('description', '');

        try {
            $ticketGroup->save();
            return $this->sendOkResponse($ticketGroup, 'Ticket Group Saved');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
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
            $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
                $query->whereUserId($user->id);
            })->first();

            if ($ticketGroup) {
                return $this->sendOkResponse($ticketGroup, 'Ticket Group Found');
            }
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        }

        $validator = Validator::make($request->all(), [
            'ticket_group_prefix' => ['required', 'string'],
            'description' => ['required']
        ]);

        if ($validator->fails()) {
            return $this->sendBadResponse(["errors" => $validator->errors()], 'Validation Failed');
        }

        $ticketGroup->ticket_group_prefix = $request->input('ticket_group_prefix', '');
        $ticketGroup->description = $request->input('description', '');

        try {
            $ticketGroup->save();
            return $this->sendOkResponse($ticketGroup, 'Ticket Group Saved');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        }

        try {
            $result = $ticketGroup->delete();
            if ($result) {
                return $this->sendOkResponse($result, 'Ticket Group Deleted');
            }
            return $this->sendBadResponse(false, "Cannot Delete Ticket Group");
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    public function ticketActive($id)
    {
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        }

        $ticketGroup->active = 1;
        $ticketGroup->activeCount++;

        try {
            $ticketGroup->save();
            return $this->sendOkResponse($ticketGroup, 'Ticket Group Activated');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }

    public function ticketInactive($id)
    {
        $user = Auth::user();

        $ticketGroup = TicketGroup::where('id', $id)->whereHas('queue_setting', function (Builder $query) use ($user) {
            $query->whereUserId($user->id);
        })->first();

        if (!$ticketGroup) {
            return $this->sendBadResponse(null, 'Ticket Group Not found');
        }

        $ticketGroup->active = 0;

        try {
            $ticketGroup->save();
            return $this->sendOkResponse($ticketGroup, 'Ticket Group Inactivated');
        } catch (\Throwable $th) {
            return $this->sendErrorResponse($th->getMessage(), 'DB Error');
        }
    }
}
