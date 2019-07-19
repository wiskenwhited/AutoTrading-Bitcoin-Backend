<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCyclePurchases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cycle_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cycle_id');
            $table->string('coin');
            $table->float('ati');
            $table->dateTime('last_purchased_at');
            $table->timestamps();

            $table->foreign('cycle_id')
                ->references('id')
                ->on('cycles')
                ->onDelete('cascade');

            $table->unique(['cycle_id', 'coin']);
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
