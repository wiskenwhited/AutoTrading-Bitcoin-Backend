<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketSummary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('market_summary', function (Blueprint $table) {
            $table->string('exchange_id');
            $table->string('market_name');
            $table->double('high');
            $table->double('low');
            $table->double('volume');
            $table->double('last');
            $table->double('base_volume');
            $table->dateTime('time_stamp');
            $table->double('bid');
            $table->double('ask');
            $table->unsignedInteger('open_buy_orders');
            $table->unsignedInteger('open_sell_orders');
            $table->double('prev_day');
            $table->dateTime('created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('market_summary');
    }
}
