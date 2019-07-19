<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateExchangeAccountsAddWithdrawalSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exchange_accounts', function (Blueprint $table) {
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
