<?php

namespace App\Models;

/**
 * App\Models\QueueSetting
 *
 * @property int $id
 * @property string $display_name
 * @property string $detail
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\QueueCalendarSetting[] $queue_calendar_setting
 * @property-read int|null $queue_calendar_setting_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketGroup[] $ticket_group
 * @property-read int|null $ticket_group_count
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting whereDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting whereUserId($value)
 * @mixin \Eloquent
 * @property int $line_config_id
 * @method static \Illuminate\Database\Eloquent\Builder|QueueSetting whereLineConfigId($value)
 */

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
