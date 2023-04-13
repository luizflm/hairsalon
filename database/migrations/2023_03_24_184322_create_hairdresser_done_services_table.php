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
        Schema::create('hairdresser_done_services', function (Blueprint $table) {
            $table->id();
            $table->dateTime('service_datetime');
            $table->unsignedBigInteger('hairdresser_id');
            $table->unsignedBigInteger('hairdresser_service_id');
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
        Schema::table('hairdresser_done_services', function (Blueprint $table) {
            $table->dropForeign('service_id');
            $table->dropForeign('hairdresser_id');
        });
        Schema::dropIfExists('hairdresser_done_services');
    }
};
