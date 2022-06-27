<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Line\LineConfig;
use App\Models\QueueSetting;
use App\Models\TicketGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingStatusController extends Controller
{
    function checkAllSettingStatus()
    {
        $user = Auth::user();

        $settingStatus = [];

        $line_setting = LineConfig::first(); //Demo
        $settingStatus['line'] = $line_setting ? true : false;

        $queue_setting = QueueSetting::whereUserId($user->id)->first();
        $settingStatus['queue'] = $queue_setting ? true : false;

        $ticket_group_setting_count = $queue_setting ? TicketGroup::whereQueueSettingId($queue_setting->id)->count() : 0;
        $settingStatus['ticket_group'] = $ticket_group_setting_count;

        return $this->sendOkResponse($settingStatus, "OK");
    }
}
