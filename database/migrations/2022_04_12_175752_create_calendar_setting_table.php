<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCalendarSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calendar_setting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_setting_id')->constrained('queue_setting')->cascadeOnUpdate()->cascadeOnDelete()->comment("Queue Setting ID");
            $table->date('calendar_date')->comment("Queue month");
            $table->time('business_time_open')->comment('Business Hour Open');
            $table->time('business_time_close')->comment('Business Hour Close');
            $table->json("day_off")->default('[]')->comment("Unavailable Date of month");
            $table->time('allocate_time')->default('01:00:00')->comment('Allocate time per booking');
            $table->unsignedtinyInteger('booking_limit')->default(1)->comment('Number of boking in one period of time');
            $table->unsignedTinyInteger('active')->default(0)->comment('0=Draft, 1=active');
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
        Schema::table('calendar_setting', function (Blueprint $table) {
            $table->dropForeign(['queue_setting_id']);
        });
        Schema::dropIfExists('calendar_setting');
    }
}
