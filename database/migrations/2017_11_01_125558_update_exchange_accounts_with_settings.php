<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateExchangeAccountsWithSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exchange_accounts', function (Blueprint $table) {
            $table->boolean('auto_global_is_auto_trading')->default(false);
            $table->unsignedSmallInteger('auto_global_round_duration')->nullable();
            $table->enum('auto_global_round_granularity', ['hours', 'days'])->nullable();
            $table->unsignedSmallInteger('auto_global_cycles')->nullable();
            $table->enum('auto_global_age', [2, 3])->nullable();
            $table->enum('auto_entry_minimum_fr', [25, 50, 75, 100])->nullable();
            $table->enum('auto_entry_price_movement', ['progressive', 'regressive'])->nullable();
            $table->enum('auto_entry_price_sign', ['any', 'positive', 'negative'])->nullable();
            $table->enum('auto_entry_volume_movement', ['progressive', 'regressive'])->nullable();
            $table->enum('auto_entry_volume_sign', ['any', 'positive', 'negative'])->nullable();
            $table->decimal('auto_entry_maximum_ati', 40, 30)->nullable();
            $table->enum('auto_entry_ati_movement', ['progressive', 'regressive'])->nullable();
            $table->enum('auto_entry_ati_sign', ['any', 'positive', 'negative'])->nullable();
            $table->decimal('auto_entry_minimum_liquidity_variance', 40, 30)->nullable();
            $table->decimal('auto_entry_minimum_prr', 40, 30)->nullable();
            $table->enum('auto_entry_hold_time_granularity', ['minutes', 'hours'])->nullable();
            $table->unsignedSmallInteger('auto_entry_hold_time')->nullable();
            $table->enum('auto_entry_price', ['low', 'current'])->nullable();
            $table->decimal('auto_entry_position_btc', 40, 30)->nullable();
            $table->unsignedSmallInteger('auto_entry_open_time')->nullable();
            $table->enum('auto_exit_action', ['sell', 'move'])->nullable();
            $table->unsignedSmallInteger('auto_exit_intervals')->nullable();
            $table->unsignedSmallInteger('auto_exit_drops')->nullable();
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
