<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQueueCalendarSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('queue_calendar_setting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_setting_id')->constrained('queue_setting')->cascadeOnUpdate()->cascadeOnDelete()->comment("Queue Setting ID");
            $table->date('calendar_date')->comment("Queue month");
            $table->time('business_time_open')->comment('Business Hour Open');
            $table->time('business_time_close')->comment('Business Hour Close');
            $table->json("day_off")->default('[]')->comment("Unavailable Date of month");
            $table->time('allocate_time')->default('01:00:00')->comment('Allocate time per queue');
            $table->unsignedtinyInteger('queue_on_allocate')->comment('Number of queue in one allocate time');
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
        Schema::table('queue_calendar_setting', function (Blueprint $table) {
            $table->dropForeign(['queue_setting_id']);
        });
        Schema::dropIfExists('queue_calendar_setting');
    }
}
