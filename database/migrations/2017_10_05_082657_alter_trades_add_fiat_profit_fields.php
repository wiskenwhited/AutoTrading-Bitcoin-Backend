<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTradesAddFiatProfitFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->string('local_currency')->nullable();
            $table->double('price_per_unit_local_currency')->nullable();
            $table->double('btc_price_usd')->nullable();
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
            Schema::table('trades', function (Blueprint $table) {
                $table->dropColumn('local_currency');
                $table->dropColumn('price_per_unit_local_currency');
                $table->dropColumn('btc_price_usd');
            });
        }
    }
}
