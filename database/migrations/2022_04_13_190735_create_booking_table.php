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
            $table->foreignId('queue_calendar_setting_id')->constrained('queue_calendar_setting')->cascadeOnUpdate()->cascadeOnDelete()->comment("Queue Calendar Setting ID");
            $table->foreignId('line_member_id')->constrained('line_member')->cascadeOnUpdate()->cascadeOnDelete()->comment("Line Member ID");
            $table->foreignId('status')->constrained('ma_booking_status')->restrictOnUpdate()->restrictOnDelete()->comment('ma_booking_status ID');
            $table->string('booking_code')->nullable()->comment('Random unique string');
            $table->string('customer_name', 150)->comment('Customer Name');
            $table->string('customer_contact', 100)->comment('Contact');
            $table->dateTime('booking_date')->comment('Booking date/time');
            $table->dateTime('confirmed_date')->nullable()->comment('Booking confirmed Datetime');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()->comment("Confirmed by User ID");
            $table->dateTime('reject_date')->nullable()->comment('Booking reject date time');
            $table->foreignId('reject_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()->comment("Reject by User ID");
            $table->dateTime('revise_date')->nullable()->comment('Booking Revise date time');
            $table->foreignId('revise_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()->comment("Revise by User ID");
            $table->dateTime('complete_date')->nullable()->comment('Booking complete date time');
            $table->foreignId('complete_by')->nullable()->constrained('users')->cascadeOnUpdate()->cascadeOnDelete()->comment("Complete by User ID");
            $table->dateTime('cancel_date')->nullable()->comment('Customer Cancel Datetime');
            $table->dateTime('lost_date')->nullable()->comment('Customer Lost Datetime');
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
            $table->dropForeign(['queue_calendar_setting_id']);
            $table->dropForeign(['line_member_id']);
            $table->dropForeign(['status']);
        });
        Schema::dropIfExists('booking');
    }
}
