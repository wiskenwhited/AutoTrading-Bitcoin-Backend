<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSuggestionsTableAddDiffs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APP_ENV') == 'testing') {
            Schema::table('suggestions', function (Blueprint $table) {
                $table->double('target_diff')->nullable();
                $table->double('exchange_trend_diff')->nullable();
                $table->integer('market_cap_diff')->nullable();
                $table->double('btc_impact_diff')->nullable();
                $table->double('impact_1hr_diff')->nullable();
                $table->double('gap_diff')->nullable();
                $table->double('cpp_diff')->nullable();
                $table->double('prr_diff')->nullable();
                $table->decimal('target_score_diff', 3, 2)->nullable();
                $table->integer('percentchange_score_diff')->nullable();
                $table->integer('marketcap_score_diff')->nullable();
                $table->integer('pricebtc_score_diff')->nullable();
                $table->integer('overall_score_diff')->nullable();
            });
        } else {
            Schema::table('suggestions', function (Blueprint $table) {
                $table->double('target_diff');
                $table->double('exchange_trend_diff');
                $table->integer('market_cap_diff');
                $table->double('btc_impact_diff');
                $table->double('impact_1hr_diff');
                $table->double('gap_diff');
                $table->double('cpp_diff');
                $table->double('prr_diff');
                $table->decimal('target_score_diff', 3, 2);
                $table->integer('percentchange_score_diff');
                $table->integer('marketcap_score_diff');
                $table->integer('pricebtc_score_diff');
                $table->integer('overall_score_diff');
            });
        }
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
                $table->dropColumn('target_diff');
                $table->dropColumn('exchange_trend_diff');
                $table->dropColumn('market_cap_diff');
                $table->dropColumn('btc_impact_diff');
                $table->dropColumn('impact_1hr_diff');
                $table->dropColumn('gap_diff');
                $table->dropColumn('cpp_diff');
                $table->dropColumn('prr_diff');
                $table->dropColumn('target_score_diff');
                $table->dropColumn('percentchange_score_diff');
                $table->dropColumn('marketcap_score_diff');
                $table->dropColumn('pricebtc_score_diff');
                $table->dropColumn('overall_score_diff');
            });
        }
    }
}
