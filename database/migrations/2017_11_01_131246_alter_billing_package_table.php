<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBillingPackageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_packages', function (Blueprint $table) {
            $table->integer('sms')->nullable();
            $table->integer('emails')->nullable();
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
            Schema::table('billing_packages', function (Blueprint $table) {
                $table->dropColumn('sms');
                $table->dropColumn('emails');
            });
        }
    }
}
