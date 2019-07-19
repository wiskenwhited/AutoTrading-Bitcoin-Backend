<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeanTradeTableIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mean_trade_values', function (Blueprint $table) {
//            $table->dropIndex(['exchange', 'coin', 'level']);
            $table->unique(['exchange', 'coin', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mean_trade_values', function (Blueprint $table) {
            $table->dropUnique(['exchange', 'coin', 'level']);
        });
    }
}
