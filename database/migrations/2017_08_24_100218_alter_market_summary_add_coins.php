<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMarketSummaryAddCoins extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APP_ENV') == 'testing') {
            Schema::table('market_summary', function (Blueprint $table) {
                $table->string('base_coin_id', 10)->after('market_name')->nullable();
                $table->string('target_coin_id', 10)->after('base_coin_id')->nullable();
            });
        } else {
            Schema::table('market_summary', function (Blueprint $table) {
                $table->string('base_coin_id', 10)->after('market_name');
                $table->string('target_coin_id', 10)->after('base_coin_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APP_ENV') !== 'testing') {
            Schema::table('market_summary', function (Blueprint $table) {
                $table->dropColumn('base_coin_id');
                $table->dropColumn('target_coin_id');
            });
        }
    }
}
