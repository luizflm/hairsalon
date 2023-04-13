<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hairdresser_evaluations', function (Blueprint $table) {
            $table->id();
            $table->integer('stars')->default(0);
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('hairdresser_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE');
            $table->foreign('hairdresser_id')
                ->references('id')
                ->on('hairdressers')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hairdresser_evaluations', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropForeign('hairdresser_id');
        });
        Schema::dropIfExists('hairdresser_evaluations');
    }
};
