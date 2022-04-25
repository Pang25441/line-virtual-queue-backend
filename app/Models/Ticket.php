<?php

namespace App\Models;

use App\Models\Line\LineMember;
use App\Models\Master\MaTicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Ticket
 *
 * @property int $id
 * @property int $ticket_group_id
 * @property int $line_member_id
 * @property int $status
 * @property int $ticket_group_active_count Queue Ticket Group Active Count
 * @property string $pending_time Ticket print date time
 * @property string|null $calling_time Queue Calling time
 * @property string|null $executed_time Queue Start Process
 * @property string|null $postpone_time Queue postpone time
 * @property string|null $reject_time Queue rejected time
 * @property string|null $lost_time Queue Lost time
 * @property int $is_postpone Is queue was postpone
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\TicketGroup $ticket_group
 * @property-read MaTicketStatus|null $ticket_status
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCallingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereExecutedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereIsPostpone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereLineMemberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereLostTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket wherePendingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket wherePostponeTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereRejectTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTicketGroupActiveCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTicketGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $count Queue Number Counter
 * @property string $ticket_number Ticket number
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ticket whereTicketNumber($value)
 * @property-read LineMember|null $line_member
 */
class Ticket extends Model
{
    use HasFactory;

    protected $table = "ticket";

    protected $with = ['ticket_status'];

    function ticket_status()
    {
        return $this->hasOne(MaTicketStatus::class, 'id', 'status');
    }

    function ticket_group()
    {
        return $this->belongsTo(TicketGroup::class, 'ticket_group_id');
    }

    function line_member() {
        return $this->hasOne(LineMember::class, 'line_member_id');
    }
}
