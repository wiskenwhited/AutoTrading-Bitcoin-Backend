<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradesTable extends Migration
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
            $table->unsignedInteger('suggestion_id')->nullable();
            $table->string('order_uuid');
            $table->string('exchange');
            $table->string('exchange_id');
            $table->string('currency_id');
            $table->double('price_bought');
            $table->double('cpp');
            $table->double('gap');
            $table->double('profit');
            $table->string('order_type');
            $table->double('quantity');
            $table->double('quantity_remaining');
            $table->double('limit');
            $table->double('reserved');
            $table->double('reserved_remaining');
            $table->double('commission_reserved');
            $table->double('commission_reserved_remaining');
            $table->double('commission_paid');
            $table->double('price');
            $table->double('price_per_unit');
            $table->dateTime('opened');
            $table->dateTime('closed');
            $table->boolean('is_open');
            $table->string('sentinel');
            $table->boolean('cancel_initiated');
            $table->boolean('immediate_or_cancel');
            $table->boolean('is_conditional');
            $table->string('condition');
            $table->string('condition_target')->nullable();
            $table->string('suggestion');
            $table->string('status');
            $table->string('exit_strategy');
            $table->string('shrink_differential');
            $table->boolean('target_price');

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
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
        Schema::dropIfExists('trades');
    }
}
