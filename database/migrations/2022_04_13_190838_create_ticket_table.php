<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_group_id')->constrained('queue_group')->cascadeOnUpdate()->cascadeOnDelete()->comment("Queue Setting ID");
            $table->foreignId('line_member_id')->constrained('line_member')->cascadeOnUpdate()->cascadeOnDelete()->comment("Line Member ID");
            $table->unsignedInteger('queue_group_active_count')->comment('Queue Group Active Count');
            $table->unsignedTinyInteger('status')->default(0)->comment('Queue, Called, Executed, Reject, Lost');
            $table->dateTime('queue_time')->useCurrent()->comment('Ticket print date time');
            $table->dateTime('call_time')->nullable()->comment('Queue Call time');
            $table->dateTime('execute_time')->nullable()->comment('Queue Start Process');
            $table->dateTime('postpone_time')->nullable()->comment('Queue postpone time');
            $table->dateTime('reject_time')->nullable()->comment('Queue rejected time');
            $table->unsignedTinyInteger('is_postpone')->default(0)->comment('Is queue was postpone');
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
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropForeign(['queue_group_id']);
            $table->dropForeign(['line_member_id']);
        });
        Schema::dropIfExists('ticket');
    }
}
