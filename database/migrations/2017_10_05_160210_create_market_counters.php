<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketCounters extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('market_counters', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('ts');
            $table->string('exchange');
            $table->string('coin');
            $table->integer('cpp_up')->default(0);
            $table->integer('cpp_down')->default(0);
            $table->integer('btc_bought_up')->default(0);
            $table->integer('btc_bought_down')->default(0);
            $table->integer('btc_sold_up')->default(0);
            $table->integer('btc_sold_down')->default(0);

            $table->index(['ts', 'exchange', 'coin']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('market_counters');
    }
}
