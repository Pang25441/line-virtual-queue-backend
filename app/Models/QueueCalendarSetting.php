<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueCalendarSetting extends Model
{
    use HasFactory;

    protected $table = "queue_calendar_setting";

    protected $casts = [
        'day_off' => 'array',
    ];

}
