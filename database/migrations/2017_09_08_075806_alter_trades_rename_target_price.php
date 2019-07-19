<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTradesRenameTargetPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->double('starting_shrink_differential')->nullable();
        });
        Schema::table('trades', function (Blueprint $table) {
            $table->renameColumn('target_price', 'target_percent');
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
            Schema::table('trades', function (Blueprint $table) {
                $table->renameColumn('target_percent', 'target_price');
                $table->dropColumn('starting_shrink_differential');
            });
        }
    }
}
