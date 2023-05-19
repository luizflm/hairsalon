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
            $table->boolean('was_done')->default(0);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
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
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropForeign('hairdresser_id');
            $table->dropForeign('hairdresser_service_id');
        });
        Schema::dropIfExists('appointments');
    }
};
