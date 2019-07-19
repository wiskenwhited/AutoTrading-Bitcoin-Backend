<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTradingJobsTableAddDispatchCountAndArguments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trading_bot_jobs', function (Blueprint $table) {
            $table->unsignedSmallInteger('dispatch_count')->default(0);
            $table->text('job_arguments')->nullable();
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
            Schema::table('trading_bot_jobs', function (Blueprint $table) {
                $table->dropColumn(['dispatch_count', 'job_arguments']);
            });
        }
    }
}
