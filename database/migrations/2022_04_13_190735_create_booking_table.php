<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_setting_id')->constrained('queue_setting')->cascadeOnUpdate()->cascadeOnDelete()->comment("Queue Setting ID");
            $table->foreignId('queue_calendar_setting_id')->constrained('queue_calendar_setting')->cascadeOnUpdate()->cascadeOnDelete()->comment("Queue Calendar Setting ID");
            $table->foreignId('line_member_id')->constrained('line_member')->cascadeOnUpdate()->cascadeOnDelete()->comment("Line Member ID");
            $table->string('customer_name', 150)->comment('Customer Name');
            $table->string('customer_contact', 100)->comment('Contact');
            $table->dateTime('booking_date')->comment('Booking date/time');
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
        Schema::table('booking', function(Blueprint $table){
            $table->dropForeign(['queue_setting_id']);
            $table->dropForeign(['queue_calendar_setting_id']);
            $table->dropForeign(['line_member_id']);
        });
        Schema::dropIfExists('booking');
    }
}
