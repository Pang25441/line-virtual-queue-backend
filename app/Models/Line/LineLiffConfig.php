<?php

namespace App\Models\Line;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineLiffConfig extends Model
{
    use HasFactory;

    protected $table = "line_liff_config";

    function line_config()
    {
        return $this->belongsTo(LineConfig::class, 'line_config_id');
    }
}
