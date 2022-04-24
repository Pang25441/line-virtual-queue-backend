<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CalendarSetting
 *
 * @property int $id
 * @property int $queue_setting_id
 * @property string $calendar_date Queue month
 * @property string $business_time_open Business Hour Open
 * @property string $business_time_close Business Hour Close
 * @property array $day_off Unavailable Date of month
 * @property string $allocate_time Allocate time per queue
 * @property int $queue_on_allocate Number of queue in one allocate time
 * @property int $active 0=Draft, 1=active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\QueueSetting $queue_setting
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereAllocateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereBusinessTimeClose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereBusinessTimeOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereCalendarDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereDayOff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereQueueOnAllocate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereQueueSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $booking_limit Number of boking in one period of time
 * @method static \Illuminate\Database\Eloquent\Builder|CalendarSetting whereBookingLimit($value)
 */
class CalendarSetting extends Model
{
    use HasFactory;

    protected $table = "calendar_setting";

    protected $casts = [
        'day_off' => 'array',
    ];

    function queue_setting()
    {
        return $this->belongsTo(QueueSetting::class, 'queue_setting_id');
    }

}
