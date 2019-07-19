<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSuggestionsTableAddExchange extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suggestions', function(Blueprint $table) {
            $table->string('exchange')->after('id')->nullable();
            if (! Schema::hasColumn('suggestions', 'impact_1hr')) {
                $table->renameColumn('1hr_impact', 'impact_1hr');
            }
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
            Schema::table('suggestions', function (Blueprint $table) {
                $table->dropColumn('exchange');
            });
        }
    }
}
