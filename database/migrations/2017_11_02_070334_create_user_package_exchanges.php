<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPackageExchanges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_package_exchanges', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('user_package_id');
            $table->string('exchange');
            $table->boolean('live_enabled');
            $table->dateTime('live_started')->nullable();
            $table->dateTime('live_valid_until')->nullable();
            $table->integer('active_days_eligible')->default(0);
            $table->timestamps();


            $table->foreign('user_package_id')
                ->references('id')
                ->on('user_packages')
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
        Schema::dropIfExists('user_package_exchanges');
    }
}
