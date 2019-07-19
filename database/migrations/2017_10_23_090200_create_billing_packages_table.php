<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('package_name');
            $table->decimal('price', 10, 8);
            $table->string('description', 1000);
            $table->boolean('enabled')->default(true);
            $table->boolean('is_feature')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_packages');
    }
}
