<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistHistoryTableAddPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist_history', function (Blueprint $table) {
            $table->double('price_per_unit')->nullable();
        });
        Schema::table('watchlist_rules', function (Blueprint $table) {
            $table->dropColumn('sell_target');
        });
        Schema::table('watchlist_rules', function (Blueprint $table) {
            $table->double('sell_target')->nullable();
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
            Schema::table('watchlist_history', function (Blueprint $table) {
                $table->dropColumn('price_per_unit');
            });

            Schema::table('watchlist_rules', function (Blueprint $table) {
                $table->dropColumn('sell_target');
            });
            Schema::table('watchlist_rules', function (Blueprint $table) {
                $table->unsignedInteger('sell_target')->nullable();
            });
        }
    }
}
