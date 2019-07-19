<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->string('id');
            $table->string('name');
            $table->string('symbol');
            $table->integer('rank');
            $table->float('price_usd')->nullable();
            $table->float('price_btc')->nullable();
            $table->float('volume_usd_24h')->nullable();
            $table->float('market_cap_usd')->nullable();
            $table->float('available_supply')->nullable();
            $table->float('total_supply')->nullable();
            $table->float('percent_change_1h')->nullable();
            $table->float('percent_change_24h')->nullable();
            $table->float('percent_change_7d')->nullable();
            $table->dateTimeTz('last_updated')->nullable();
            $table->timestamps();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('coins');
    }
}
