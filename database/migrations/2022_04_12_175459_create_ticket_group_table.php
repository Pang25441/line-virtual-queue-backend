<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_setting_id')->constrained('queue_setting')->cascadeOnUpdate()->cascadeOnDelete()->comment("Queue Setting ID");
            $table->string('unique_key')->nullable()->comment('Random unique key');
            $table->unsignedTinyInteger('active')->default(0)->comment("Active Status");
            $table->unsignedInteger("active_count")->default(0)->comment("Running Number");
            $table->tinyText("ticket_group_prefix")->default('')->comment("Queue Group Prefix");
            $table->string("description", 100)->comment("Description");
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
        Schema::table('ticket_group', function (Blueprint $table) {
            $table->dropForeign(['queue_setting_id']);
        });
        Schema::dropIfExists('ticket_group');
    }
}
