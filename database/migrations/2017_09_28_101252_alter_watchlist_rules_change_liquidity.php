<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistRulesChangeLiquidity extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist_rules', function (Blueprint $table) {
            if (env('APP_ENV') !== 'testing') {
                $table->dropColumn('follow_liquidity_buy');
                $table->dropColumn('liquidity_buy_rule');
                $table->dropColumn('liquidity_buy');

                $table->dropColumn('follow_liquidity_sell');
                $table->dropColumn('liquidity_sell_rule');
                $table->dropColumn('liquidity_sell');
            }

            $table->boolean('follow_liquidity')->default(0)->after('market_cap');
            $table->unsignedInteger('liquidity_rule')->nullable()->after('follow_liquidity');
            $table->double('liquidity')->nullable()->after('liquidity_rule');
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
            Schema::table('watchlist_rules', function (Blueprint $table) {
                $table->dropColumn('follow_liquidity');
                $table->dropColumn('liquidity_rule');
                $table->dropColumn('liquidity');

                $table->boolean('follow_liquidity_buy')->default(0)->after('market_cap');
                $table->unsignedInteger('liquidity_buy_rule')->nullable()->after('follow_liquidity_buy');
                $table->double('liquidity_buy')->nullable()->after('liquidity_buy_rule');

                $table->boolean('follow_liquidity_sell')->default(0)->after('liquidity_buy');
                $table->unsignedInteger('liquidity_sell_rule')->nullable()->after('follow_liquidity_sell');
                $table->double('liquidity_sell')->nullable()->after('liquidity_sell_rule');
            });
        }
    }
}
