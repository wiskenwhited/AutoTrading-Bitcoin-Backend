<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMeanTradeValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mean_trade_values', function(Blueprint $table) {
            $table->increments('id');
            $table->string('exchange');
            $table->string('coin');
            $table->unsignedInteger('num_buys');
            $table->string('level');
            $table->double('mean_buy_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
