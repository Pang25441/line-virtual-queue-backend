<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Line\LineConfig
 *
 * @property int $id
 * @property string|null $line_id Line Official Account ID
 * @property string $channel_id Line Messaging API Channel ID
 * @property string $channel_access_token Line Messaging API Chennel Access Token
 * @property string $login_channel_id Line Login Channel ID
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\Line\LineLiffConfig|null $line_liff_config
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig query()
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig whereChannelAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig whereLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig whereLoginChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineConfig whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class LineConfig extends Model
{
    use HasFactory;

    protected $table = "line_config";

    protected $with = ['line_liff_config'];

    function line_liff_config()
    {
        return $this->hasOne(LineLiffConfig::class, 'line_config_id');
    }
}
