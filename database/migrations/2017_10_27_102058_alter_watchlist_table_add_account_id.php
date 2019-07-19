<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWatchlistTableAddAccountId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist', function (Blueprint $table) {
            $table->unsignedInteger('exchange_account_id')->after('exchange')->nullable();

            $table->foreign('exchange_account_id')
                ->references('id')
                ->on('exchange_accounts')
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
        Schema::table('watchlist', function (Blueprint $table) {
            $table->dropForeign('watchlist_exchange_account_id_foreign');
            $table->dropColumn('exchange_account_id');
        });
    }
}
