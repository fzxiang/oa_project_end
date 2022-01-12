<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPowerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_power', function (Blueprint $table) {
            $table->bigInteger('user_id')->default(0)->comment('用户唯一ID');
            $table->bigInteger('shop_id')->default(0)->comment('店铺ID');
            $table->longText('menu')->nullable()->comment('权限标签配置');
            $table->timestamps();
            $table->primary(['user_id', 'shop_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_power');
    }
}
