<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExchangeAccountsTableAddName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('exchange_accounts', function(Blueprint $table) {
            if (! Schema::hasColumn('exchange_accounts', 'id')) {
                $table->increments('id');
            }
            $table->string('name')->nullable();
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
            Schema::table('exchange_accounts', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
    }
}
