<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinmarketdata extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coinmarketdata', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('ts');
            $table->string('exchange');
            $table->string('coin');
            $table->double('cpp');
            $table->double('gap');
            $table->double('btc_bought');
            $table->double('btc_sold');
            $table->double('lowest_ask');
            $table->double('highest_bid');

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
        Schema::dropIfExists('coinmarketdata');
    }
}
