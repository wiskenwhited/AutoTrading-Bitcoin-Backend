<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSuggestionsUpdateFieldTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->dropColumn(['market_cap', 'market_cap_diff']);
        });
        Schema::table('suggestions', function (Blueprint $table) {
            $table->double('market_cap')->default(0);
            $table->double('market_cap_diff')->nullable();
            $table->decimal('target_score', 3, 2)->nullable()->change();
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
