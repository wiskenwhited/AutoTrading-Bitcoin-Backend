<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUserPackagesDropColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_packages', function (Blueprint $table) {
            $table->dropColumn(['package_id',
                'single_live_enabled',
                'single_live_started',
                'single_live_valid_until',
                'exchanges_used'
            ]);
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
            Schema::table('user_packages', function (Blueprint $table) {
                $table->integer('package_id');
                $table->integer('exchanges_used');
                $table->boolean('single_live_enabled')->after('all_live_valid_until');
                $table->dateTime('single_live_started')->nullable()->after('single_live_enabled');
                $table->dateTime('single_live_valid_until')->nullable()->after('single_live_started');
            });
        }
    }
}
