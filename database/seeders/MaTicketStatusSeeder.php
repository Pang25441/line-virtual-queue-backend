<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class MaTicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        TicketStatus::insertOrIgnore([
            ['id' => 1, 'code' => 'PENDING', 'name' => 'Pending', 'description' => 'Pending queue'],
            ['id' => 2, 'code' => 'CALLING', 'name' => 'Calling', 'description' => 'Calling queue'],
            ['id' => 3, 'code' => 'EXECUTED', 'name' => 'Executed', 'description' => 'Excuted queue'],
            ['id' => 4, 'code' => 'REJECTED', 'name' => 'Rejected', 'description' => 'Rejected queue'],
            ['id' => 5, 'code' => 'LOST', 'name' => 'Lost', 'description' => 'Lost queue'],
        ]);
    }
}
