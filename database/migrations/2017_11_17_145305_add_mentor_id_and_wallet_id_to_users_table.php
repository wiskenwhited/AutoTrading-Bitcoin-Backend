<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMentorIdAndWalletIdToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('mentor_id')->nullable()->default(NULL);
            $table->string('wallet_id', 255)->nullable()->default(NULL);

            $table->foreign('mentor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
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
                $table->dropColumn('mentor_id');
                $table->dropColumn('wallet_id');
            });
        }
    }
}
