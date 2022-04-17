<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\QueueCalendarSetting
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
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereAllocateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereBusinessTimeClose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereBusinessTimeOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereCalendarDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereDayOff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereQueueOnAllocate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereQueueSettingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueCalendarSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class QueueCalendarSetting extends Model
{
    use HasFactory;

    protected $table = "queue_calendar_setting";

    protected $casts = [
        'day_off' => 'array',
    ];

    function queue_setting()
    {
        return $this->belongsTo(QueueSetting::class, 'queue_setting_id');
    }

}
