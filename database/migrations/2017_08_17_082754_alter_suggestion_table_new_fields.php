<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSuggestionTableNewFields extends Migration
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
                $table->double('btc_liquidity_bought')->after('prr')->nullable();
                $table->double('btc_liquidity_sold')->after('btc_liquidity_bought')->nullable();

                $table->unsignedInteger('btc_impact_score')->after('target_score')->nullable();
                $table->unsignedInteger('btc_liquidity_score')->after('btc_impact_score')->nullable();
                $table->unsignedInteger('exchange_trend_score')->after('target_score')->nullable();
                $table->unsignedInteger('impact_1hr_change_score')->after('exchange_trend_score')->nullable();

                $table->unsignedInteger('exchange_trend_score_diff')->after('target_score_diff')->nullable();
                $table->unsignedInteger('btc_impact_score_diff')->after('exchange_trend_score_diff')->nullable();
                $table->unsignedInteger('btc_liquidity_score_diff')->after('btc_impact_score_diff')->nullable();
                $table->unsignedInteger('impact_1hr_change_score_diff')->after('btc_liquidity_score_diff')->nullable();

                $table->string('exchange_trend_arrow')->after('overall_score')->nullable();
                $table->string('impact_1hr_change_arrow')->after('overall_score')->nullable();
                $table->string('market_cap_arrow')->after('overall_score')->nullable();
                $table->string('btc_impact_arrow')->after('overall_score')->nullable();
                $table->string('overall_score_arrow')->after('overall_score')->nullable();
                $table->string('cpp_arrow')->after('overall_score')->nullable();
                $table->string('prr_arrow')->after('overall_score')->nullable();
                $table->string('gap_arrow')->after('overall_score')->nullable();
            });
            /*
            Schema::table('suggestions', function (Blueprint $table) {
                $table->dropColumn([
                    'percentchange_score',
                    'percentchange_score_diff',
                    'pricebtc_score',
                    'pricebtc_score_diff'
                ]);

                $table->renameColumn('marketcap_score', 'market_cap_score');
                $table->renameColumn('marketcap_score_diff', 'market_cap_score_diff');
            });
            */
        } else {
            Schema::table('suggestions', function (Blueprint $table) {
                $table->double('btc_liquidity_bought')->after('prr');
                $table->double('btc_liquidity_sold')->after('btc_liquidity_bought');

                $table->unsignedInteger('btc_impact_score')->after('target_score');
                $table->unsignedInteger('btc_liquidity_score')->after('btc_impact_score');
                $table->unsignedInteger('exchange_trend_score')->after('target_score');
                $table->unsignedInteger('impact_1hr_change_score')->after('exchange_trend_score');

                $table->unsignedInteger('exchange_trend_score_diff')->after('target_score_diff');
                $table->unsignedInteger('btc_impact_score_diff')->after('exchange_trend_score_diff');
                $table->unsignedInteger('btc_liquidity_score_diff')->after('btc_impact_score_diff');
                $table->unsignedInteger('impact_1hr_change_score_diff')->after('btc_liquidity_score_diff');

                $table->string('exchange_trend_arrow')->after('overall_score')->nullable();
                $table->string('impact_1hr_change_arrow')->after('overall_score')->nullable();
                $table->string('market_cap_arrow')->after('overall_score')->nullable();
                $table->string('btc_impact_arrow')->after('overall_score')->nullable();
                $table->string('overall_score_arrow')->after('overall_score')->nullable();
                $table->string('cpp_arrow')->after('overall_score')->nullable();
                $table->string('prr_arrow')->after('overall_score')->nullable();
                $table->string('gap_arrow')->after('overall_score')->nullable();

                $table->dropColumn('percentchange_score');
                $table->dropColumn('percentchange_score_diff');
                $table->dropColumn('pricebtc_score');
                $table->dropColumn('pricebtc_score_diff');
                $table->renameColumn('marketcap_score', 'market_cap_score');
                $table->renameColumn('marketcap_score_diff', 'market_cap_score_diff');
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
                $table->dropColumn('btc_liquidity_bought');
                $table->dropColumn('btc_liquidity_sold');
                $table->dropColumn('btc_impact_score');
                $table->dropColumn('btc_liquidity_score');
                $table->dropColumn('exchange_trend_score');
                $table->dropColumn('impact_1hr_change_score');
                $table->dropColumn('exchange_trend_score_diff');
                $table->dropColumn('btc_impact_score_diff');
                $table->dropColumn('btc_liquidity_score_diff');
                $table->dropColumn('impact_1hr_change_score_diff');
                $table->dropColumn('exchange_trend_arrow');
                $table->dropColumn('impact_1hr_change_arrow');
                $table->dropColumn('market_cap_arrow');
                $table->dropColumn('btc_impact_arrow');
                $table->dropColumn('overall_score_arrow');
                $table->dropColumn('cpp_arrow');
                $table->dropColumn('prr_arrow');
                $table->dropColumn('gap_arrow');
                $table->decimal('percentchange_score', 3, 2)->after('target_score');
                $table->decimal('percentchange_score_diff', 3, 2)->after('target_score_diff');
                $table->renameColumn('market_cap_score', 'marketcap_score');
                $table->renameColumn('market_cap_score_diff', 'marketcap_score_diff');
                $table->decimal('pricebtc_score', 3, 2)->after('target_score');
                $table->decimal('pricebtc_score_diff', 3, 2)->after('target_score');
            });
        }
    }
}
