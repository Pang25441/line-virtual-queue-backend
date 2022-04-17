<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BookingStatus
 *
 * @property int $id
 * @property string $code Code
 * @property string $name Parameter Name
 * @property string $description Description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BookingStatus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BookingStatus extends Model
{
    use HasFactory;

    protected $table = "ma_booking_status";

}
