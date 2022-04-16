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
            $table->foreignId('ticket_group_id')->constrained('ticket_group')->cascadeOnUpdate()->cascadeOnDelete()->comment("Queue Ticket Group ID");
            $table->foreignId('line_member_id')->constrained('line_member')->cascadeOnUpdate()->cascadeOnDelete()->comment("Line Member ID");
            $table->foreignId('status')->constrained('ma_ticket_status')->restrictOnDelete()->restrictOnDelete()->comment('ma_ticket_status ID');
            $table->unsignedInteger('ticket_group_active_count')->comment('Queue Ticket Group Active Count');
            $table->dateTime('pending_time')->useCurrent()->comment('Ticket print date time');
            $table->dateTime('calling_time')->nullable()->comment('Queue Calling time');
            $table->dateTime('executed_time')->nullable()->comment('Queue Start Process');
            $table->dateTime('postpone_time')->nullable()->comment('Queue postpone time');
            $table->dateTime('reject_time')->nullable()->comment('Queue rejected time');
            $table->dateTime('lost_time')->nullable()->comment('Queue Lost time');
            $table->unsignedTinyInteger('is_postpone')->default(0)->comment('Is queue was postpone');
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
        Schema::table('ticket', function (Blueprint $table) {
            $table->dropForeign(['ticket_group_id']);
            $table->dropForeign(['line_member_id']);
            $table->dropForeign(['status']);
        });
        Schema::dropIfExists('ticket');
    }
}
