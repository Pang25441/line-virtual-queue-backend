<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLineConfigIdToQueueSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('queue_setting', function (Blueprint $table) {
            $table->foreignId('line_config_id')->after('id')->constrained('line_config')->cascadeOnUpdate()->cascadeOnDelete()->comment("Line Confnig ID");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('queue_setting', function (Blueprint $table) {
            $table->dropForeign(['line_config_id']);
        });
    }
}
