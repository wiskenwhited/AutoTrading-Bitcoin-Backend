<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistRulesAddNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist_rules', function (Blueprint $table) {
            $table->unsignedInteger('cpp_rule')->nullable()->change();
            $table->unsignedInteger('prr_rule')->nullable()->change();
            $table->unsignedInteger('gap_rule')->nullable()->change();
            $table->unsignedInteger('market_cap_rule')->nullable()->change();
            $table->unsignedInteger('liquidity_buy_rule')->nullable()->change();
            $table->unsignedInteger('liquidity_sell_rule')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('watchlist_rules', function (Blueprint $table) {
            //
        });
    }
}
