<?php

namespace App\Models;

use App\Models\Master\MaBookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = "booking";

    function booking_status()
    {
        return $this->hasOne(MaBookingStatus::class, 'status');
    }

    function calendar_setting()
    {
        return $this->belongsTo(QueueCalendarSetting::class, 'queue_calendar_setting_id');
    }
}
