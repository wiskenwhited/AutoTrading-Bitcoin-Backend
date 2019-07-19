<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistHistoryAddArrows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist_history', function (Blueprint $table) {

            $table->unsignedInteger('gap_ups')->default(0);
            $table->unsignedInteger('gap_downs')->default(0);

            $table->unsignedInteger('cpp_ups')->default(0);
            $table->unsignedInteger('cpp_downs')->default(0);

            $table->unsignedInteger('prr_ups')->default(0);
            $table->unsignedInteger('prr_downs')->default(0);

            $table->unsignedInteger('liquidity_ups')->default(0);
            $table->unsignedInteger('liquidity_downs')->default(0);

            $table->unsignedInteger('market_cap_ups')->default(0);
            $table->unsignedInteger('market_cap_downs')->default(0);
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
            Schema::table('watchlist_history', function (Blueprint $table) {
                $table->dropColumn('gap_ups');
                $table->dropColumn('gap_downs');
                $table->dropColumn('cpp_ups');
                $table->dropColumn('cpp_downs');
                $table->dropColumn('prr_ups');
                $table->dropColumn('prr_downs');
                $table->dropColumn('liquidity_ups');
                $table->dropColumn('liquidity_downs');
                $table->dropColumn('market_cap_ups');
                $table->dropColumn('market_cap_downs');
            });
        }
    }
}
