<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHashOrderMapingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hash_order_maping', function (Blueprint $table) {
            $table->string('strHash')->default('')->comment('hash字断');
            $table->longText('orderIds')->nullable()->comment('hash对应订单');
            $table->integer('operate')->default(0)->comment('该hash生成时间');
            $table->primary(['strHash']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hash_order_maping');
    }
}
