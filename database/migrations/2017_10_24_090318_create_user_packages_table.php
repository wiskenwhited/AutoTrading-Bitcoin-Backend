<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('package_id')->nullable();

            $table->integer('sms_max')->nullable();
            $table->integer('sms_used')->nullable();

            $table->integer('email_max')->nullable();
            $table->integer('email_used')->nullable();

            $table->boolean('all_live_enabled');
            $table->dateTime('all_live_started')->nullable();
            $table->dateTime('all_live_valid_until')->nullable();


            $table->boolean('single_live_enabled');
            $table->dateTime('single_live_started')->nullable();
            $table->dateTime('single_live_valid_until')->nullable();


            $table->boolean('test_enabled');
            $table->dateTime('test_started')->nullable();
            $table->dateTime('test_valid_until')->nullable();

            $table->integer('exchanges_used');

            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_packages');
    }
}
