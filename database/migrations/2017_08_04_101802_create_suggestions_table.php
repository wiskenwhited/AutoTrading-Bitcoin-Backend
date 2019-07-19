<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuggestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suggestions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('coin', 3);
            $table->double('target');
            $table->double('exchange_trend');
            $table->integer('market_cap');
            $table->double('btc_impact');
            $table->double('impact_1hr');
            $table->double('gap');
            $table->double('cpp');
            $table->double('prr');
            $table->decimal('target_score', 3, 2);
            $table->decimal('percentchange_score', 3, 2);
            $table->decimal('marketcap_score', 3, 2);
            $table->decimal('pricebtc_score', 3, 2);
            $table->decimal('overall_score', 3, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suggestions');
    }
}
