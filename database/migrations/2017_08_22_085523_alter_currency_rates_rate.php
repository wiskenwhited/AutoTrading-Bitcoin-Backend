<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCurrencyRatesRate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('currency_rates', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
        if (env('APP_ENV') == 'testing') {
            Schema::table('currency_rates', function (Blueprint $table) {
                $table->double('rate')->after('target')->nullable();
            });
        } else {
            Schema::table('currency_rates', function (Blueprint $table) {
                $table->double('rate')->after('target');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (env('APP_ENV') !== 'testing') {
            Schema::table('currency_rates', function (Blueprint $table) {
                $table->dropColumn('rate');
            });
            Schema::table('currency_rates', function (Blueprint $table) {
                $table->decimal('rate')->after('target');
            });
        }
    }
}
