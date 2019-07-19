<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersUpdateEntrySettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('global_round', [1,30])->nullable();
            $table->enum('global_cycle_granularity', ['hours', 'days'])->nullable();
            $table->unsignedSmallInteger('global_cycle')->nullable();
            $table->enum('entry_minimum_fr', [25, 50, 75, 100])->nullable();
            $table->enum('entry_price_movement', ['progressive', 'regressive'])->nullable();
            $table->enum('entry_price_from', [2,3])->nullable();
            $table->enum('entry_volume_movement', ['progressive', 'regressive'])->nullable();
            $table->enum('entry_volume_from', [2,3])->nullable();
            $table->decimal('entry_ati', 40, 30)->nullable();
            $table->enum('entry_ati_movement', ['progressive', 'regressive'])->nullable();
            $table->enum('entry_ati_from', [2,3])->nullable();
            $table->decimal('entry_liquidity_variance', 40, 30)->nullable();
            $table->decimal('entry_minimum_prr', 40, 30)->nullable();
            $table->enum('entry_hold_time_granularity', ['minutes', 'hours'])->nullable();
            $table->unsignedSmallInteger('entry_hold_time')->nullable();
            $table->enum('entry_price', ['low', 'current'])->nullable();
            $table->enum('entry_historic_target_from', [1, 2, 3])->nullable();
            $table->decimal('entry_historic_target', 40, 30)->nullable();
            $table->decimal('entry_position_btc', 40, 30)->nullable();
            $table->unsignedSmallInteger('entry_open_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APP_ENV') !== 'testing') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'global_round',
                    'global_cycle_granularity',
                    'global_cycle',
                    'entry_minimum_fr',
                    'entry_price_movement',
                    'entry_price_from',
                    'entry_volume_movement',
                    'entry_volume_from',
                    'entry_ati',
                    'entry_ati_movement',
                    'entry_ati_from',
                    'entry_liquidity_variance',
                    'entry_minimum_prr',
                    'entry_hold_time_granularity',
                    'entry_hold_time',
                    'entry_price',
                    'entry_historic_target_from',
                    'entry_historic_target',
                    'entry_position_btc',
                    'entry_open_time'
                ]);
            });
        }
    }
}
