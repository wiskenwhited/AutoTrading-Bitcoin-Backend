<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCoinsTableChangeFloat extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coins', function(Blueprint $table) {
            $table->string('price_usd')->nullable()->change();
            $table->string('price_btc')->nullable()->change();
            $table->string('volume_usd_24h')->nullable()->change();
            $table->string('market_cap_usd')->nullable()->change();
            $table->string('available_supply')->nullable()->change();
            $table->string('total_supply')->nullable()->change();
            $table->string('percent_change_1h')->nullable()->change();
            $table->string('percent_change_24h')->nullable()->change();
            $table->string('percent_change_7d')->nullable()->change();
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
            Schema::table('coins', function (Blueprint $table) {
                $table->float('price_usd')->nullable()->change();
                $table->float('price_btc')->nullable()->change();
                $table->float('volume_usd_24h')->nullable()->change();
                $table->float('market_cap_usd')->nullable()->change();
                $table->float('available_supply')->nullable()->change();
                $table->float('total_supply')->nullable()->change();
                $table->float('percent_change_1h')->nullable()->change();
                $table->float('percent_change_24h')->nullable()->change();
                $table->float('percent_change_7d')->nullable()->change();
            });
        }
    }
}
