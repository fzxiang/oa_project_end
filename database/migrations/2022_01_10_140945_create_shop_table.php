<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShopTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop', function (Blueprint $table) {
            $table->bigInteger('shop_id', true);
            $table->string('shop_name')->default('')->comment('店铺名');
            $table->string('company_name')->default('')->comment('公司名');
            $table->longText('remarks')->nullable()->comment('备注');
            $table->bigInteger('create_user')->default(0)->comment('创建者用户ID');
            $table->bigInteger('update_user')->default(0)->comment('更新者用户ID');
            $table->timestamps();
            $table->unique(['shop_name'], 'shop_req');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop');
    }
}
