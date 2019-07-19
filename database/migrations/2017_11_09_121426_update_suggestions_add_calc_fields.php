<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateSuggestionsAddCalcFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suggestions', function (Blueprint $table) {
            $table->double('ati_percentage_difference')->nullable();
            $table->double('recommended_entry_price')->nullable();
            $table->double('new_mean_buy_time')->nullable();
            $table->double('mid_mean_buy_time')->nullable();
            $table->double('old_mean_buy_time')->nullable();
            $table->double('buy_volume_3hr')->nullable();
            $table->double('buy_volume_2hr')->nullable();
            $table->double('buy_volume_1hr')->nullable();
            $table->double('highest_price_3hr')->nullable();
            $table->double('highest_price_2hr')->nullable();
            $table->double('highest_price_1hr')->nullable();
            $table->double('lowest_price_3hr')->nullable();
            $table->double('lowest_price_2hr')->nullable();
            $table->double('lowest_price_1hr')->nullable();
            $table->double('derived_target_3hr')->nullable();
            $table->double('derived_target_2hr')->nullable();
            $table->double('derived_target_1hr')->nullable();
            $table->double('ati_lowest_price')->nullable();
            $table->double('ati_highest_price')->nullable();
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
