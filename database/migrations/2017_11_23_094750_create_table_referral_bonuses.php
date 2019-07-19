<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableReferralBonuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_bonuses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mentor_id')->nullable();
            $table->unsignedInteger('mentee_id')->nullable();
            $table->unsignedInteger('package_id')->nullable();
            $table->unsignedInteger('billing_history_id')->nullable();
            $table->decimal('total_price', 10, 8)->nullable();
            $table->decimal("mentor_bonus_perc")->nullable();
            $table->decimal("mentor_bonus_to_pay")->nullable();
            $table->boolean('sent')->boolean(false);
            $table->timestamps();


            $table->foreign('mentor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('mentee_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('billing_history_id')
                ->references('id')
                ->on('billing_history')
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
        Schema::dropIfExists('referral_bonuses');
    }
}
