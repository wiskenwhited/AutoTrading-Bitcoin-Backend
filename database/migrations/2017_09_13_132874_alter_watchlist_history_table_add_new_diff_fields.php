<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistHistoryTableAddNewDiffFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::table('watchlist_history', function (Blueprint $table) {
                $table->double('target_diff')->nullable();
                $table->double('exchange_trend_diff')->nullable();
                $table->double('btc_impact_diff')->nullable();
                $table->double('impact_1hr_diff')->nullable();
                $table->double('gap_diff')->nullable();
                $table->double('cpp_diff')->nullable();
                $table->double('prr_diff')->nullable();
                $table->decimal('target_score_diff', 3, 2)->nullable();
                $table->unsignedInteger('exchange_trend_score_diff')->nullable();
                $table->unsignedInteger('btc_impact_score_diff')->nullable();
                $table->unsignedInteger('btc_liquidity_score_diff')->nullable();
                $table->double('btc_liquidity_bought_diff')->default(0);
                $table->double('btc_liquidity_sold_diff')->default(0);
                $table->unsignedInteger('impact_1hr_change_score_diff')->nullable();
                $table->integer('market_cap_score_diff')->nullable();
                $table->integer('overall_score_diff')->nullable();
                $table->double('market_cap_diff')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
