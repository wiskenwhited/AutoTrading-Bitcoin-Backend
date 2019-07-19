<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistAddModeType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist', function (Blueprint $table) {
            $table->boolean('is_test')->default(0)->after('id');
            $table->boolean('execute_sell')->default(0)->after('execute');
            $table->unsignedInteger('smart_sell_interval')->nullable()->after('interval');
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
            Schema::table('watchlist', function (Blueprint $table) {
                $table->dropColumn('is_test');
                $table->dropColumn('execute_sell');
                $table->dropColumn('smart_sell_interval');
            });
        }
    }
}
