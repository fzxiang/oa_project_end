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
            $table->string('writerNum')->default('')->comment('写手手机号');
            $table->string('name')->default('')->comment('写手名');
            $table->string('alipayAccount')->default('')->comment('写手支付宝');
            $table->string('qqAccount')->default('')->comment('写手QQ');
            $table->string('wechatAccount')->default('')->comment('写手微信');
            $table->string('writerSituation')->default('')->comment('写手情况');
            $table->string('writerQuality')->default('')->comment('写手质量');
            $table->unique(['writerNum'], 'unique1');
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
