<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RecreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('order_uuid');
            $table->string('partial_trade_id')->nullable();
            $table->string('exchange_id');
            $table->string('base_coin_id');
            $table->string('target_coin_id');
            $table->string('exchange_pair')->nullable();
            $table->string('order_type')->nullable();
            $table->double('quantity')->nullable();
            $table->double('quantity_remaining')->nullable();
            $table->double('limit')->nullable();
            $table->double('reserved')->nullable();
            $table->double('reserved_remaining')->nullable();
            $table->double('commission_reserved')->nullable();
            $table->double('commission_reserved_remaining')->nullable();
            $table->double('commission_paid')->nullable();
            $table->double('price')->nullable();
            $table->double('price_per_unit')->nullable();
            $table->dateTime('opened')->nullable();
            $table->dateTime('closed')->nullable();
            $table->boolean('is_open')->nullable();
            $table->string('sentinel')->nullable();
            $table->boolean('cancel_initiated')->nullable();
            $table->boolean('immediate_or_cancel')->nullable();
            $table->boolean('is_conditional')->nullable();
            $table->string('condition')->nullable();
            $table->string('condition_target')->nullable();
            $table->string('status')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('exchange_id')
                ->references('id')
                ->on('exchanges')
                ->onDelete('cascade');

            $table->index(['order_uuid']);
            $table->index(['partial_trade_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trades');
    }
}
