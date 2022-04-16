<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_config', function (Blueprint $table) {
            $table->id();
            $table->string('line_id', 50)->nullable()->comment('Line Official Account ID');
            $table->string('channel_id', 50)->comment('Line Messaging API Channel ID');
            $table->string('channel_access_token', 255)->comment('Line Messaging API Chennel Access Token');
            $table->string('login_channel_id', 50)->comment('Line Login Channel ID');
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
        Schema::dropIfExists('line_config');
    }
}
