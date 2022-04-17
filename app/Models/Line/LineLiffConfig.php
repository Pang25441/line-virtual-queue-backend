<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Line\LineLiffConfig
 *
 * @property int $id
 * @property int $line_config_id
 * @property string|null $ticket_liff_app_id LIFF ID - Ticket App
 * @property string|null $booking_liff_app_id LIFF ID - Booking App
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Line\LineConfig $line_config
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig whereBookingLiffAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig whereLineConfigId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig whereTicketLiffAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineLiffConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LineLiffConfig extends Model
{
    use HasFactory;

    protected $table = "line_liff_config";

    function line_config()
    {
        return $this->belongsTo(LineConfig::class, 'line_config_id');
    }
}
