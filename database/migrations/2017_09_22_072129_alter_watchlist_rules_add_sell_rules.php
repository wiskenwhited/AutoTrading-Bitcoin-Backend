<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistRulesAddSellRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist_rules', function (Blueprint $table) {

            $table->unsignedInteger('smart_sell_drops')->nullable();
            $table->unsignedInteger('sell_target')->nullable();
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
                $table->dropColumn('smart_sell_drops')->nullable();
                $table->dropColumn('sell_target')->nullable();
            });
        }
    }
}
