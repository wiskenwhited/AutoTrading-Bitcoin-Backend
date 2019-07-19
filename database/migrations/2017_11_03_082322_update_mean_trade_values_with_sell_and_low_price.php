<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMeanTradeValuesWithSellAndLowPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mean_trade_values', function (Blueprint $table) {
            $table->unsignedInteger('num_sells')->nullable();
            $table->double('mean_sell_time')->nullable();
            $table->double('lowest_price')->nullable();
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
