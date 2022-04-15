<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineLiffConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_liff_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_config_id')->constrained('line_config')->cascadeOnUpdate()->cascadeOnDelete()->comment("Line Confnig ID");
            $table->string('ticket_app', 50)->nullable()->comment('LIFF ID - Ticket App');
            $table->string('booking_app', 50)->nullable()->comment('LIFF ID - Booking App');
            $table->string('remark', 255)->default('')->comment('Remark');
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
        Schema::table('line_liff_config', function (Blueprint $table) {
            $table->dropForeign(['line_config_id']);
        });
        Schema::dropIfExists('line_liff_config');
    }
}
