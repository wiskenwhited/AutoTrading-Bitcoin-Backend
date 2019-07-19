<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSuggestionsAddLiquidityDiff extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->double('btc_liquidity_bought_diff')->after('btc_liquidity_score_diff')->default(0);
            $table->double('btc_liquidity_sold_diff')->after('btc_liquidity_bought_diff')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APP_ENV') !== 'testing') {
            Schema::table('suggestions', function (Blueprint $table) {
                $table->dropColumn('btc_liquidity_bought_diff');
                $table->dropColumn('btc_liquidity_sold_diff');
            });
        }
    }
}
