<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovedWhIdIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('watchlist_history', function (Blueprint $table) {
            $table->dropIndex('watchlist_history_id_index');
            $table->dropIndex('watchlist_history_watchlist_id_index');
            $table->index(['watchlist_id', 'created_at']);
            $table->index(['watchlist_id', 'id']);

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
                $table->index('id');
                $table->index('watchlist_id_index');
            });
        }
    }
}
