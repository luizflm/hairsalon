<?php

use App\Models\HairdresserAvailability;
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
        Schema::create('hairdresser_availability', function (Blueprint $table) {
            $table->id();
            $table->integer('weekday');
            $table->text('hours');
            $table->foreignId('hairdresser_id')->constrained()->onDelete('cascade');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hairdresser_availability', function (Blueprint $table) {
            $table->dropForeign('hairdresser_id');
        });
        Schema::dropIfExists('hairdresser_availability');
    }
};
