<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScratchCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scratch_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code');
            $table->unsignedInteger('user_id')->nullable();
            $table->timestamps();

            $table->unique(['code', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scratch_codes');
    }
}
