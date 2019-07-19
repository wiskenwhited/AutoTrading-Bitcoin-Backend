<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTradesAddGapBought extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->double('gap_bought')->nullable();
            $table->unsignedInteger('original_trade_id')->nullable();

            $table->foreign('original_trade_id')
                ->references('id')
                ->on('trades')
                ->onDelete('set null');
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
                $table->dropColumn('gap_bought');
                $table->dropForeign(['original_trade_id']);
                $table->dropColumn('original_trade_id');
            });
        }
    }
}
