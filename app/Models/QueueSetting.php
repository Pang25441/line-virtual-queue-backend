<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueSetting extends Model
{
    use HasFactory;

    protected $table = "queue_setting";

    function queue_calendar_setting() {
        return $this->hasMany(QueueCalendarSetting::class, 'queue_setting_id');
    }

    function ticket_group() {
        return $this->hasMany(TicketGroup::class, 'queue_setting_id');
    }

}
