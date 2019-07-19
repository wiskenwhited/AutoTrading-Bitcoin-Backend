<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSuggestionsChangeScores extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suggestions', function(Blueprint $table) {
            $table->unsignedInteger('percentchange_score')->change();
            $table->unsignedInteger('marketcap_score')->change();
            $table->unsignedInteger('pricebtc_score')->change();
            $table->unsignedInteger('overall_score')->change();
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
                $table->decimal('percentchange_score', 3, 2)->change();
                $table->decimal('marketcap_score', 3, 2)->change();
                $table->decimal('pricebtc_score', 3, 2)->change();
                $table->decimal('overall_score', 3, 2)->change();
            });
        }
    }
}
