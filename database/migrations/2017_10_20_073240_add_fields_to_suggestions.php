<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToSuggestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suggestions', function(Blueprint $table) {
            $table->unsignedInteger('num_buys')->default(0);
            $table->double('mean_buy_time')->default(0);
            $table->unsignedInteger('num_sells')->default(0);
            $table->double('mean_sell_time')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
