<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersAddSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->double('entry_frugality_ratio')->nullable();
            $table->double('entry_price_relativity_ratio')->nullable();
            $table->boolean('entry_notified_by_email')->default(false);
            $table->boolean('entry_notified_by_sms')->default(false);
            $table->boolean('entry_is_auto_trading')->default(false);
            $table->double('exit_target')->nullable();
            $table->double('exit_shrink_differential')->nullable();
            $table->string('exit_option')->nullable();
            $table->boolean('exit_notified_by_email')->default(false);
            $table->boolean('exit_notified_by_sms')->default(false);
            $table->boolean('exit_is_auto_trading')->default(false);
            $table->double('withdrawal_capital_balance')->nullable();
            $table->string('withdrawal_capital_balance_currency')->nullable();
            $table->double('withdrawal_value')->nullable();
            $table->string('withdrawal_value_coin')->nullable();
            $table->text('withdrawal_address')->nullable();
            $table->boolean('withdrawal_notified_by_email')->default(false);
            $table->boolean('withdrawal_notified_by_sms')->default(false);
            $table->boolean('withdrawal_is_auto_trading')->default(false);
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
