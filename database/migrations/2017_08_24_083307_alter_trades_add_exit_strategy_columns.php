<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTradesAddExitStrategyColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->double('current_shrink_differential')->nullable();
            $table->double('target_shrink_differential')->nullable();
            $table->double('target_price')->nullable();
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
            Schema::table('trades', function (Blueprint $table) {
                $table->dropColumn('current_shrink_differential');
                $table->dropColumn('target_shrink_differential');
                $table->dropColumn('target_price');
            });
        }
    }
}
