<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('user_id', true);
            $table->bigInteger('shop_id')->default(0)->comment('用户选择中店铺');
            $table->string('username')->default('')->comment('用户名');
            $table->string('nickname')->default('')->comment('昵称');
            $table->string('password');
            $table->bigInteger('role_id')->default(0)->comment('所属角色ID');
            $table->rememberToken();
            $table->timestamps();
            $table->unique(['username'], 'user_account_req');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
