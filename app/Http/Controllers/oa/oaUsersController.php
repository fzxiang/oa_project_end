<?php

namespace App\Http\Controllers\oa;

use App\Http\Controllers\Controller;
use App\Libs\TokenSsl;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;

class oaUsersController extends Controller
{
    public function __construct()
    {
//        $this->middleware('auth', [
//            'except' => ['store']
//        ]);
//
//        $this->middleware('guest', [
//            'only' => ['create']
//        ]);
    }

    // 用户登陆
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'name' => 'required',
            'password' => 'required'
        ]);

        if (!$ret = Auth::attempt($credentials)) {
            // 登陆失败（密码不匹配）
            return 'false';
        }

        $ret = Auth::user()->toArray();
        $input = [
            'user_id' => $ret['user_id'],
            'name' => $ret['name']
        ];

        $token = TokenSsl::encryptOpenssl($input, TokenSsl::TOKEN_KEY);

        return ['token' => $token];
    }

    // token获取玩家数据
    public static function getUserIdOfToken($token)
    {
        if (!$token) {
            return false;
        }

        $data = TokenSsl::decryptOpenssl($token, TokenSsl::TOKEN_KEY);
        if (!$data) {
            return false;
        }

        return $data;
    }

    // 权限获取
    public function power(Request $request)
    {
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($request['token'])) {
            return 'err_token';
        }

        // todo 权限管理

        // 权限查询
        $count = DB::table('user_power')->where('user_id', '=', $data['user_id'])->count();
        $userPowers = DB::table('user_power')->where('user_id', '=', $data['user_id'])->get()->toArray();

        return ['count' => $count, 'data' => $userPowers];
    }

    // 添加用户
    public function addUser(Request $request)
    {
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($request['token'])) {
            return 'err_token';
        }

        // todo 权限管理

        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $password = $request['password'];
        $user = User::create([
            'name' => $request['name'],
            'nickname' => $request['nickname'],
            'password' => bcrypt($password),
        ]);

        // 权限处理
        $time = date('Y-m-d H:i:s');
        $arr = [];
        $jsonConf = $request['powerJson'] ?? [];
        $powerJson = $request['powerJson'] ? json_decode($jsonConf, true) : [];
        if ($powerJson) {
            foreach ($powerJson as $conf) {
                $sqlArr = [
                    'user_id' => $data['user_id'],
                    'shop_id' => $conf['shopId'],
                    'menu' => json_encode($conf['power'], true),
                    'created_at' => $time,
                    'updated_at' => $time,
                ];
                $arr[] = $sqlArr;
            }
        }

        $arr && $bool = DB::table('user_power')->insert($arr);

        return $user;
    }

    // 初始化对应用户密码
    public function initPwd(Request $request)
    {
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($request['token'])) {
            return 'err_token';
        }

        // todo 权限管理

        // 未传参数
        $initUid = $request['uId'];
        if (!$initUid) {
            return 'err_param';
        }

        $bool = DB::table('users')->where('user_id', '=', $initUid)->update(['password' => bcrypt('123456')]);

        return $bool;
    }

    // 删除对应用户
    public function delUser(Request $request)
    {
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($request['token'])) {
            return 'err_token';
        }

        // todo 权限管理

        // 未传参数
        $initUid = $request['uId'];
        if (!$initUid) {
            return 'err_param';
        }

        $bool = DB::table('users')->where('user_id', '=', $initUid)->delete();

        return $bool;
    }

    // 更新对应用户权限
    public function updatePower(Request $request)
    {
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($request['token'])) {
            return 'err_token';
        }

        // todo 权限管理

        // 未传参数
        if (!$request['uId']) {
            return 'err_param';
        }

        // 删除旧权限
        $delBool = DB::table('user_power')->where('user_id', '=', $request['uId'])->delete();

        if (!$delBool) {
            return $delBool;
        }

        // 更新权限
        $time = date('Y-m-d H:i:s');
        $arr = [];
        $jsonConf = $request['powerJson'] ?? [];
        $powerJson = $request['powerJson'] ? json_decode($jsonConf, true) : [];
        if ($powerJson) {
            foreach ($powerJson as $conf) {
                $sqlArr = [
                    'user_id' => $data['user_id'],
                    'shop_id' => $conf['shopId'],
                    'menu' => json_encode($conf['power'], true),
                    'created_at' => $time,
                    'updated_at' => $time,
                ];
                $arr[] = $sqlArr;
            }
        }

        $arr && $bool = DB::table('user_power')->insert($arr);

//        $data = DB::table('shop')->whereRaw('shop_id = ? and shop_name = ?', [1, 'aaa'])->get()->toArray();
        return $bool;
    }

    // 新增商店
    public function addShop(Request $request)
    {
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($request['token'])) {
            return 'err_token';
        }

        // todo 权限管理

        if (!$request['shopName']) {
            return 'err_param';
        }

        $shop = Shop::create([
            'shop_name' => $request['shopName'],
            'company_name' => $request['companyName'],
            'remarks' => $request['remarks'],
            'create_user' => $data['user_id'],
            'update_user' => $data['user_id'],
        ]);

        return $shop;
    }

    // 获取所有用户
    public function getUsers(Request $request)
    {
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($request['token'])) {
            return 'err_token';
        }

        $data = User::all()->toArray();

        return $data;
    }

    // 获取所有商店
    public function getShops(Request $request)
    {
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($request['token'])) {
            return 'err_token';
        }

        $data = Shop::all()->toArray();

        return $data;
    }

}
