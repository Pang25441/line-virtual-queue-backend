<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Master\MaBookingStatus
 *
 * @property int $id
 * @property string $code Code
 * @property string $name Parameter Name
 * @property string $description Description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MaBookingStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MaBookingStatus extends Model
{
    use HasFactory;

    protected $table = "ma_booking_status";
}
