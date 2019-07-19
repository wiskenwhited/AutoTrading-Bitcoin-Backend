<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist', function (Blueprint $table) {
            $table->string('base_coin_id', 11)->default('BTC')->after('exchange');
            $table->string('type')->default('buy')->after('id');
            $table->unsignedInteger('trade_id')->nullable();
            $table->double('price')->nullable();
            $table->double('price_per_unit')->nullable();
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
            Schema::table('watchlist', function (Blueprint $table) {
                $table->dropColumn('base_coin_id');
                $table->dropColumn('type');
                $table->dropColumn('trade_id');
                $table->dropColumn('price');
                $table->dropColumn('price_per_unit');
            });
        }
    }
}
