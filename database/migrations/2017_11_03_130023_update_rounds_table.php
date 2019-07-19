<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateRoundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });
        Schema::table('rounds', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'data_json']);
        });
        Schema::table('rounds', function (Blueprint $table) {
            $table->text('holders_json')->nullable();
            $table->text('purchases_json')->nullable();
            $table->unsignedInteger('cycle_length')->nullable();
            $table->unsignedInteger('exchange_account_id')->nullable();
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
        //
    }
}
