<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWatchlistHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watchlist_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('watchlist_id');
            $table->double('target');
            $table->double('exchange_trend');
            $table->double('btc_impact');
            $table->double('impact_1hr');
            $table->double('gap');
            $table->double('cpp');
            $table->double('prr');
            $table->double('btc_liquidity_bought');
            $table->double('btc_liquidity_sold');
            $table->decimal('target_score', 3, 2)->nullable();
            $table->unsignedInteger('exchange_trend_score');
            $table->unsignedInteger('impact_1hr_change_score');
            $table->unsignedInteger('btc_impact_score');
            $table->unsignedInteger('btc_liquidity_score');
            $table->unsignedInteger('market_cap_score');
            $table->unsignedInteger('overall_score');
            $table->string('base',8)->nullable();
            $table->double('lowest_ask')->nullable();
            $table->double('highest_bid')->nullable();
            $table->double('market_cap')->nullable();
            $table->dateTime('time_of_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('watchlist_history');
    }
}
