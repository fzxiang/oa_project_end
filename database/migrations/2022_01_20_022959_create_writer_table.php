<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWriterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('writer', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('shop_id')->default(0)->comment('商店ID');
            $table->string('writerNum')->default('')->comment('写手手机号');
            $table->string('name')->default('')->comment('写手名');
            $table->string('alipayAccount')->default('')->comment('写手支付宝');
            $table->string('qqAccount')->default('')->comment('写手QQ');
            $table->string('wechatAccount')->default('')->comment('写手微信');
            $table->string('writerSituation')->default('')->comment('写手情况');
            $table->string('writerQuality')->default('')->comment('写手质量');
            $table->unique(['shop_id', 'writerNum'], 'unique1');
            $table->unique(['shop_id', 'alipayAccount'], 'unique2');
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
        Schema::dropIfExists('writer');
    }
}
