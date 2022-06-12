<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * App\Models\TicketGroup
 *
 * @property int $id
 * @property int $queue_setting_id
 * @property string|null $unique_key Random unique key
 * @property int $active Active Status
 * @property int $active_count Running Number
 * @property string|null $ticket_group_prefix Queue Group Prefix
 * @property string $description Description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\QueueSetting $queue_setting
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereActiveCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereQueueSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereTicketGroupPrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereUniqueKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property string|null $ticket_group_code Random unique key
 * @method static \Illuminate\Database\Eloquent\Builder|TicketGroup whereTicketGroupCode($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $current_ticket
 * @property-read int|null $current_ticket_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Ticket[] $tickets
 * @property-read int|null $tickets_count
 */
class TicketGroup extends Model
{
    use HasFactory;

    protected $table = "ticket_group";

    function queue_setting()
    {
        return $this->belongsTo(QueueSetting::class, 'queue_setting_id');
    }

    function tickets()
    {
        return $this->hasMany(Ticket::class, 'ticket_group_id', 'id');
    }

    function current_ticket() {
        return $this->hasMany(Ticket::class, 'ticket_group_active_count', 'active_count');
    }

}
