<?php

namespace App\Models;

use App\Models\Master\MaTicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $table = "ticket";

    function ticket_status()
    {
        return $this->hasOne(MaTicketStatus::class, 'status');
    }

    function ticket_group()
    {
        return $this->belongsTo(TicketGroup::class, 'ticket_group_id');
    }
}
