<?php

namespace App\Http\Controllers\oa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class oaUsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['store']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    // 用户登陆
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'password' => 'required'
        ]);

        if (!Auth::attempt($credentials)) {
            // 登陆失败（密码不匹配）
            return 'false';
        }

        return 'success';
    }

    // 权限获取
    public function power()
    {

        return [2,2,2];
    }

    // 添加用户
    public function addUser(Request $request)
    {
        return [3,3,3];
    }

    // 初始化对应用户密码
    public function initPwd(Request $request)
    {
        return [4,4,4];
    }

    // 删除对应用户
    public function delUser(Request $request)
    {
        return [5,5,5];
    }

    // 更新对应用户权限
    public function updatePower(Request $request)
    {
        return [6,6,6];
    }

    // 新增商店
    public function addShop(Request $request)
    {
        return [7,7,7];
    }
}
