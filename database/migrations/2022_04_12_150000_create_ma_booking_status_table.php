<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaBookingStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ma_booking_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Parameter Name');
            $table->string('value', 100)->comment('Value');
            $table->string('description', 255)->comment('Description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ma_booking_status');
    }
}
