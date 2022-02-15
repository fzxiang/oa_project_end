<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shop_id')->default(0)->comment('商店ID');
            $table->string('invoice')->default('')->comment('发单号');
            $table->bigInteger('acceptUser')->default(0)->comment('接单客服ID');
            $table->string('aliOrder')->default('')->comment('淘宝订单编号');
            $table->integer('settleState')->default(2)->comment('客服结算状态 1:已结算,2:未结算,3:暂缓结算');
            $table->string('memberName')->default('')->comment('会员名');
            $table->float('taobaoPrice')->default(0)->comment('淘宝价格');
            $table->string('customerContact')->default('')->comment('客户微信或QQ');
            $table->longText('orderOutline')->nullable()->comment('订单概要');
            $table->longText('remarks')->nullable()->comment('备注');
            $table->integer('paymentTime')->default(0)->comment('订单付款时间');
            $table->integer('receivingTime')->default(0)->comment('订单收货时间');
            $table->float('overviewFilePrice')->default(0)->comment('总览附件打款商家金额');
            $table->float('actualPaymentPrice')->default(0)->comment('买家实际支付金额');
            $table->float('refundPrice')->default(0)->comment('买家退款金额');
            $table->float('serWritePrice')->default(0)->comment('客服填写价格');
            $table->index(['paymentTime','receivingTime'], 'index1');
            $table->index(['shop_id'], 'index2');
            $table->unique(['aliOrder'], 'unique1');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order');
    }
}
