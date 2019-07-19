<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserEnable2fa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('confirmed_2fa')->default(false);
            $table->string('code_2fa')->nullable();
            $table->unsignedInteger('enabled_2fa')->default(0);
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
                $table->dropColumn('confirmed_2fa');
                $table->dropColumn('code_2fa');
                $table->dropColumn('enabled_2fa');
                $table->dropColumn('code_2fa');
            });
        }
    }
}
