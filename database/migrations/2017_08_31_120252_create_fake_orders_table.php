<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFakeOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fake_orders', function (Blueprint $table) {
            $table->string('order_uuid');
            $table->string('exchange')->nullable();
            $table->string('order_type');
            $table->double('quantity');
            $table->double('quantity_remaining')->default(0);
            $table->double('limit')->default(0);
            $table->double('reserved')->default(0);
            $table->double('reserved_remaining')->default(0);
            $table->double('commission_reserved')->default(0);
            $table->double('commission_reserved_remaining')->default(0);
            $table->double('commission_paid')->default(0);
            $table->double('price')->default(0);
            $table->double('price_per_unit')->default(0);
            $table->string('opened')->nullable();
            $table->string('closed')->nullable();
            $table->boolean('is_open');
            $table->string('sentinel')->nullable();
            $table->boolean('cancel_initiated')->default(false);
            $table->boolean('immediate_or_cancel')->default(false);
            $table->boolean('is_conditional')->default(false);
            $table->string('condition')->default('NONE');
            $table->string('condition_target')->default('');
            $table->boolean('leave_open')->default(false);
            $table->timestamps();

            $table->primary('order_uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('fake_orders');
    }
}
