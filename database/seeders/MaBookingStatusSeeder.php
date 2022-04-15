<?php

namespace Database\Seeders;

use App\Models\BookingStatus;
use Illuminate\Database\Seeder;

class MaBookingStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        BookingStatus::insertOrIgnore([
            ['id' => 1, 'code' => 'PENDING', 'name' => 'Pending', 'description' => 'New Booking'],
            ['id' => 2, 'code' => 'CONFIRMED', 'name' => 'Confirmed', 'description' => 'Confirmed Booking'],
            ['id' => 3, 'code' => 'REJECTED', 'name' => 'Rejected', 'description' => 'Rejected Booking'],
            ['id' => 4, 'code' => 'REVISE', 'name' => 'Revise', 'description' => 'Revised Booking'],
            ['id' => 5, 'code' => 'DONE', 'name' => 'Done', 'description' => 'Done Booking'],
            ['id' => 6, 'code' => 'CANCELED', 'name' => 'Canceled', 'description' => 'Canceled by User'],
            ['id' => 7, 'code' => 'LOST', 'name' => 'Lost', 'description' => 'Lost Booking'],
        ]);
    }
}
