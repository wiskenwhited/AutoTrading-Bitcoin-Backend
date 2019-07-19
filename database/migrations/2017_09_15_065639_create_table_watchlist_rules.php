<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWatchlistRules extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('watchlist_rules', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('watchlist_id');
            $table->unsignedInteger('number_of_intervals');

            $table->boolean('follow_cpp')->default(false);
            $table->string('cpp_rule');
            $table->double('cpp')->nullable();

            $table->boolean('follow_prr')->default(false);
            $table->unsignedInteger('prr_rule');
            $table->double('prr')->nullable();

            $table->boolean('follow_gap')->default(false);
            $table->unsignedInteger('gap_rule');
            $table->double('gap')->nullable();

            $table->boolean('follow_market_cap')->default(false);
            $table->unsignedInteger('market_cap_rule');
            $table->double('market_cap')->nullable();

            $table->boolean('follow_liquidity_buy')->default(false);
            $table->unsignedInteger('liquidity_buy_rule');
            $table->double('liquidity_buy')->nullable();

            $table->boolean('follow_liquidity_sell')->default(false);
            $table->unsignedInteger('liquidity_sell_rule');
            $table->double('liquidity_sell')->nullable();

            $table->double('buy_amount_btc')->nullable();
            $table->double('buy_quantity')->nullable();

            $table->double('sell_amount_btc')->nullable();
            $table->double('sell_quantity')->nullable();

            $table->boolean('email_sent')->default(false);
            $table->boolean('sms_sent')->default(false);
            $table->boolean('bought')->default(false);
            $table->boolean('sold')->default(false);

            $table->timestamps();

            $table->foreign('watchlist_id')
                ->references('id')
                ->on('watchlist')
                ->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('watchlist_rules');
    }
}
