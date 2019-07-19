<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterScratchCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scratch_codes', function (Blueprint $table) {
            $table->string('type')->after('code')->default("");
            $table->boolean('assigned')->after('type')->default(false);
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
            Schema::table('scratch_codes', function (Blueprint $table) {
                $table->dropColumn('type');
                $table->dropColumn('assigned');
            });
        }
    }
}
