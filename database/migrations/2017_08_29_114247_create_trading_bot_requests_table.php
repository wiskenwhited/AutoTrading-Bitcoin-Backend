<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradingBotRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trading_bot_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('request_type');
            $table->longText('json_payload');
            $table->longText('json_response')->nullable();
            $table->text('json_meta')->nullable();
            $table->timestamps();

            $table->index('request_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trading_bot_requests');
    }
}
