<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTradesAddTradingBotRequestId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->unsignedInteger('trading_bot_request_id')->nullable();
            $table->foreign('trading_bot_request_id')
                ->references('id')
                ->on('trading_bot_requests')
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
        if (env('APP_ENV') !== 'testing') {
            Schema::table('trades', function (Blueprint $table) {
                $table->dropForeign(['trading_bot_request_id']);
            });
            Schema::table('trades', function (Blueprint $table) {
                $table->dropColumn('trading_bot_request_id');
            });
        }
    }
}
