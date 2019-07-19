<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTradesAddParentTradeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->unsignedInteger('parent_trade_id')->nullable();

            $table->foreign('parent_trade_id')
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
                $table->dropForeign(['parent_trade_id']);
                $table->dropColumn('parent_trade_id');
            });
        }
    }
}
