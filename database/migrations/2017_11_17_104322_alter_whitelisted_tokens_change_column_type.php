<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWhitelistedTokensChangeColumnType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('whitelisted_tokens', function (Blueprint $table) {
            $table->string('token', 512)->change();
            $table->index('token');
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
            Schema::table('whitelisted_tokens', function (Blueprint $table) {
                $table->text('token')->change();
                $table->dropIndex('whitelisted_tokens_token');
            });
        }
    }
}
