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
            $table->foreignId('hairdresser_id')->constrained()->onDelete('cascade');
            $table->foreignId('hairdresser_service_id')->constrained()->onDelete('cascade');
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
