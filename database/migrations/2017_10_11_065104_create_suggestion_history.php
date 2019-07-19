<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuggestionHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggestion_history', function (Blueprint $table) {
            $table->increments('id');
            $table->string('exchange');
            $table->string('coin', 8);
            $table->string('base', 8);
            $table->double('target');
            $table->double('exchange_trend');
            $table->double('btc_impact');
            $table->double('impact_1hr');
            $table->double('gap');
            $table->double('cpp');
            $table->double('prr');
            $table->double('btc_liquidity_bought');
            $table->double('btc_liquidity_sold');
            $table->double('liquidity');
            $table->double('lowest_ask')->nullable();
            $table->double('highest_bid')->nullable();
            $table->double('market_cap')->nullable();
            $table->decimal('target_score',3,2)->nullable();
            $table->integer('exchange_trend_score');
            $table->integer('impact_1hr_change_score');
            $table->integer('btc_impact_score');
            $table->integer('btc_liquidity_score');
            $table->integer('market_cap_score');
            $table->integer('overall_score');


            $table->smallInteger('target_change_up');
            $table->smallInteger('exchange_trend_change_up');
            $table->smallInteger('btc_impact_change_up');
            $table->smallInteger('impact_1hr_change_up');
            $table->smallInteger('gap_change_up');
            $table->smallInteger('cpp_change_up');
            $table->smallInteger('prr_change_up');
            $table->smallInteger('btc_liquidity_bought_change_up');
            $table->smallInteger('btc_liquidity_sold_change_up');
            $table->smallInteger('liquidity_change_up');
            $table->smallInteger('lowest_ask_change_up');
            $table->smallInteger('highest_bid_change_up');
            $table->smallInteger('market_cap_change_up');
            $table->smallInteger('target_score_change_up');
            $table->smallInteger('exchange_trend_score_change_up');
            $table->smallInteger('impact_1hr_change_score_change_up');
            $table->smallInteger('btc_impact_score_change_up');
            $table->smallInteger('btc_liquidity_score_change_up');
            $table->smallInteger('market_cap_score_change_up');
            $table->smallInteger('overall_score_change_up');

            $table->smallInteger('target_change_down');
            $table->smallInteger('exchange_trend_change_down');
            $table->smallInteger('btc_impact_change_down');
            $table->smallInteger('impact_1hr_change_down');
            $table->smallInteger('gap_change_down');
            $table->smallInteger('cpp_change_down');
            $table->smallInteger('prr_change_down');
            $table->smallInteger('btc_liquidity_bought_change_down');
            $table->smallInteger('btc_liquidity_sold_change_down');
            $table->smallInteger('liquidity_change_down');
            $table->smallInteger('lowest_ask_change_down');
            $table->smallInteger('highest_bid_change_down');
            $table->smallInteger('market_cap_change_down');
            $table->smallInteger('target_score_change_down');
            $table->smallInteger('exchange_trend_score_change_down');
            $table->smallInteger('impact_1hr_change_score_change_down');
            $table->smallInteger('btc_impact_score_change_down');
            $table->smallInteger('btc_liquidity_score_change_down');
            $table->smallInteger('market_cap_score_change_down');
            $table->smallInteger('overall_score_change_down');


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
        Schema::dropIfExists('suggestion_history');
    }
}
