<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWriterOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('writer_order', function (Blueprint $table) {
            $table->bigInteger('writerId')->default(0)->comment('写手唯一ID');
            $table->bigInteger('orderId')->default(0)->comment('订单ID');
            $table->bigInteger('shop_id')->default(0)->comment('商店ID');
            $table->integer('writerPrice')->default(0)->comment('写手派单价');
            $table->integer('wSettleState')->default(2)->comment('写手结算状态 1:已结算,2:未结算,3:暂缓结算');
            $table->integer('compensateState')->default(0)->comment('补偿状态 1:稿费补偿');
            $table->primary(['writerId', 'orderId']);
            $table->index(['shop_id'], 'index1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('writer_order');
    }
}
