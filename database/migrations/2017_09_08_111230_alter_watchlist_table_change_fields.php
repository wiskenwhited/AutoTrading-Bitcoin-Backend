<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistTableChangeFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist', function (Blueprint $table) {
            $table->dropColumn(['target_score', 'base', 'lowest_ask', 'highest_bid', 'market_cap']);
        });
        Schema::table('watchlist', function (Blueprint $table) {
            $table->decimal('target_score', 3, 2)->after('btc_liquidity_sold')->nullable();
            $table->string('base',8)->after('overall_score')->nullable();
            $table->double('lowest_ask')->after('base')->nullable();
            $table->double('highest_bid')->after('lowest_ask')->nullable();
            $table->double('market_cap')->after('highest_bid')->default(0);
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
