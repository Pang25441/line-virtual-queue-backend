<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Master\MaTicketStatus
 *
 * @property int $id
 * @property string $code Code
 * @property string $name Parameter Name
 * @property string $description Description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaTicketStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MaTicketStatus extends Model
{
    use HasFactory;

    protected $table = "ma_ticket_status";
}
