<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LineConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $line_config_id = DB::table('line_config')->insertGetId([
            'line_id' => env('LINE_ID'),
            'channel_id' => env('LINE_CHANNEL_ID'),
            'channel_access_token' => env('LINE_ACCESS_TOKEN'),
            'login_channel_id' => env('LINE_LOGIN_CHANNEL_ID')
        ]);

        DB::table('line_liff_config')->insert([
            'line_config_id' => $line_config_id,
            'ticket_liff_app_id' => env('LIFF_TICKET_APP'),
            'booking_liff_app_id' => env('LIFF_BOOKING_APP'),
        ]);
    }
}
