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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CalendarSetting[] $calendar_setting
 * @property-read int|null $calendar_setting_count
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\TicketGroup[] $ticket_groups
 * @property-read int|null $ticket_groups_count
 */

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueSetting extends Model
{
    use HasFactory;

    protected $table = "queue_setting";

    function calendar_setting() {
        return $this->hasMany(CalendarSetting::class, 'queue_setting_id');
    }

    function ticket_groups() {
        return $this->hasMany(TicketGroup::class, 'queue_setting_id');
    }

}
