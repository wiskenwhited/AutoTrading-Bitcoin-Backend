<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWatchlistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watchlist', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('exchange')->nullable();
            $table->string('coin',8)->nullable();
            $table->double('target');
            $table->double('exchange_trend');
            $table->double('btc_impact');
            $table->double('impact_1hr');
            $table->double('gap');
            $table->double('cpp');
            $table->double('prr');
            $table->double('btc_liquidity_bought');
            $table->double('btc_liquidity_sold');
            $table->decimal('target_score', 3, 2);
            $table->unsignedInteger('exchange_trend_score');
            $table->unsignedInteger('impact_1hr_change_score');
            $table->unsignedInteger('btc_impact_score');
            $table->unsignedInteger('btc_liquidity_score');
            $table->unsignedInteger('market_cap_score');
            $table->unsignedInteger('overall_score');
            $table->string('base',8)->nullable();
            $table->double('lowest_ask');
            $table->double('highest_bid');
            $table->double('market_cap');
            $table->integer('interval');
            $table->dateTime('last_check')->nullable();
            $table->boolean('sms');
            $table->boolean('email');
            $table->boolean('execute');
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
        Schema::dropIfExists('watchlist');
    }
}
