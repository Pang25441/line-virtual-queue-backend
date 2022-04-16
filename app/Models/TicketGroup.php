<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketGroup extends Model
{
    use HasFactory;

    protected $table = "ticket_group";

    function queue_setting()
    {
        return $this->belongsTo(QueueSetting::class, 'queue_setting_id');
    }
}
