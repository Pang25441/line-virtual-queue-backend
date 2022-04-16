<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
