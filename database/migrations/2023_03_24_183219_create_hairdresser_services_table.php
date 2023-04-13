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
        Schema::create('hairdresser_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('price')->default(0);
            $table->unsignedBigInteger('hairdresser_id');
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
        Schema::table('hairdresser_services', function (Blueprint $table) {
            $table->dropForeign('hairdresser_id');
        });
        Schema::dropIfExists('hairdresser_services');
    }
};
