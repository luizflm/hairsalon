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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->dateTime('ap_datetime');
            $table->boolean('was_done');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('hairdresser_id');
            $table->unsignedBigInteger('hairdresser_service_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE');
            $table->foreign('hairdresser_id')
                ->references('id')
                ->on('hairdressers')
                ->onDelete('CASCADE');
            $table->foreign('hairdresser_service_id')
                ->references('id')
                ->on('hairdresser_services')
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
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropForeign('hairdresser_id');
            $table->dropForeign('hairdresser_service_id');
        });
        Schema::dropIfExists('appointments');
    }
};
