<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TicketStatus
 *
 * @property int $id
 * @property string $code Code
 * @property string $name Parameter Name
 * @property string $description Description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TicketStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TicketStatus extends Model
{
    use HasFactory;

    protected $table = "ma_ticket_status";

}
