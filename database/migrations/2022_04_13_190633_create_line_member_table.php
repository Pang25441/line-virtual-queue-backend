<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_member', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_config_id')->constrained('line_config')->cascadeOnUpdate()->cascadeOnDelete()->comment("Line Confnig ID");
            $table->string('user_id', 255)->comment('Line Profile ID');
            $table->string('display_name', 100)->comment('Line Profile Name');
            $table->string('picture', 255)->comment('Line Profile Picture');
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
        Schema::table('line_member', function (Blueprint $table) {
            $table->dropForeign(['line_config_id']);
        });

        Schema::dropIfExists('line_member');
    }
}
