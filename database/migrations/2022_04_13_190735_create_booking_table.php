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
            $table->foreignId('status')->constrained('ma_booking_status')->restrictOnUpdate()->restrictOnDelete()->comment('ma_booking_status ID');
            $table->string('customer_name', 150)->comment('Customer Name');
            $table->string('customer_contact', 100)->comment('Contact');
            $table->dateTime('booking_date')->comment('Booking date/time');
            $table->dateTime('approve_date')->comment('Booking Approve Datetime');
            $table->foreignId('approve_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()->comment("Approved by User ID");
            $table->dateTime('reject_date')->comment('Booking reject date time');
            $table->foreignId('reject_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()->comment("Reject by User ID");
            $table->dateTime('revise_date')->comment('Booking Revise date time');
            $table->foreignId('revise_by')->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()->comment("Revise by User ID");
            $table->dateTime('cancel_date')->comment('Customer Cancel Datetime');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booking', function (Blueprint $table) {
            $table->dropForeign(['queue_setting_id']);
            $table->dropForeign(['queue_calendar_setting_id']);
            $table->dropForeign(['line_member_id']);
            $table->dropForeign(['status']);
        });
        Schema::dropIfExists('booking');
    }
}
