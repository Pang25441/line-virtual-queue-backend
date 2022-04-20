<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Line\LineMember
 *
 * @property int $id
 * @property int $line_config_id
 * @property string $user_id Line Profile ID
 * @property string $display_name Line Profile Name
 * @property string $picture Line Profile Picture
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember query()
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember whereLineConfigId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LineMember whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Line\LineConfig $line_config
 */
class LineMember extends Model
{
    use HasFactory;

    protected $table = "line_member";

    function line_config() {
        return $this->belongsTo(LineConfig::class, 'line_config_id');
    }

}
