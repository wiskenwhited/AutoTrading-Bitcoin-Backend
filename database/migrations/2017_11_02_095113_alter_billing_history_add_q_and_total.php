<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBillingHistoryAddQAndTotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('billing_history', function (Blueprint $table) {
            $table->renameColumn('paid', 'price_per_item');
            $table->integer('quantity')->after('paid')->nullable();
            $table->decimal('total_price', 10, 8)->after('quantity')->nullable();
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
            Schema::table('billing_history', function (Blueprint $table) {
                $table->renameColumn('price_per_item', 'paid');
                $table->dropColumn('quantity');
                $table->dropColumn('total_price');
            });
        }
    }
}
