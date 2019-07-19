<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->boolean('is_canceled')->default(false);
            $table->unsignedInteger('cycle_count')->default(0);
            $table->unsignedInteger('minimum_fr_count')->default(0);
            $table->unsignedInteger('price_volume_count')->default(0);
            $table->unsignedInteger('ati_count')->default(0);
            $table->unsignedInteger('limiters_count')->default(0);
            $table->longText('data_json')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rounds');
    }
}
