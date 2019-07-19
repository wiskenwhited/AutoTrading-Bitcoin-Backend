<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersSmartsellFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('smart_sell_enabled')->default(false);
            $table->integer('smart_sell_interval')->nullable();
            $table->integer('smart_sell_drops')->nullable();
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

                $table->dropColumn('smart_sell_enabled');
                $table->dropColumn('smart_sell_interval');
                $table->dropColumn('smart_sell_drops');
            });
        }
    }
}
