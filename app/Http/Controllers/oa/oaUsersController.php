<?php

namespace App\Http\Controllers\oa;

use App\Http\Controllers\Controller;
use App\Libs\TokenSsl;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public static function result($info = [], $code = 0, $message = 'ok')
    {
        $relt = [
            'code' => $code,
            'message' => $message,
            'result' => $info
        ];
        return $relt;
    }

    // 用户登陆
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'username' => 'required',
            'password' => 'required'
        ]);

        if (!$ret = Auth::attempt($credentials)) {
            // 登陆失败（密码不匹配）
            return 'false';
        }

        $ret = Auth::user()->toArray();
        $input = [
            'user_id' => $ret['user_id'],
            'username' => $ret['username']
        ];

        $token = TokenSsl::encryptOpenssl($input, TokenSsl::TOKEN_KEY);

        $relt = ['token' => $token];
        return self::result($relt);
    }

    // 用户登出
    public function logout(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        self::result();
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
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        // 权限查询
        $count = DB::table('user_power')->where('user_id', '=', $data['user_id'])->count();
        $userPowers = DB::table('user_power')->where('user_id', '=', $data['user_id'])->get()->toArray();

        $formatUserPowers = $this->powerFormat($userPowers);

        $userDB = DB::table('users')->where('user_id', '=', $data['user_id'])->get()->toArray();
        $userDB = $userDB[0];
        $relt = [
            'count' => $count,
            'permission' => $formatUserPowers,
//            'password' => decrypt($userDB->password),
            'userId' => $userDB->user_id,
            'username' => $userDB->username,
            'realName' => $userDB->nickname,
            'selectedShop' => $userDB->shop_id,
        ];
        return self::result($relt);
    }

    public function powerFormat($datas)
    {
        $formatData = [];
        foreach ($datas as $data) {
            $item['menu'] = json_decode($data->menu);
            $item['shop_id'] = $data->shop_id;
            $item['user_id'] = $data->user_id;
            // 店铺
            $shop = Shop::find($data->shop_id);
            $item['shop_name'] = $shop['shop_name'];

            $formatData[] = $item;
        }
        return $formatData;
    }

    // 添加用户
    public function addUser(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        $this->validate($request, [
            'username' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $password = $request['password'];
        $user = User::create([
            'username' => $request['username'],
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

        $relt = [
            'user' => $user,
        ];
        return self::result($relt);
    }

    // 初始化对应用户密码
    public function initPwd(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        // 未传参数
        $initUid = $request['uId'];
        if (!$initUid) {
            return 'err_param';
        }

        $bool = DB::table('users')->where('user_id', '=', $initUid)->update(['password' => bcrypt('123456')]);

        $relt = [
            'sqlBool' => $bool
        ];
        return self::result($relt);
    }

    // 删除对应用户
    public function delUser(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        // 未传参数
        $initUid = $request['uId'];
        if (!$initUid) {
            return 'err_param';
        }

        $bool = DB::table('users')->where('user_id', '=', $initUid)->delete();

        $relt = [
            'sqlBool' => $bool
        ];
        return self::result($relt);
    }

    // 更新对应用户权限
    public function updatePower(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        // 未传参数
        if (!$request['uId']) {
            return self::result([],-1, 'err_param');
        }

        // 删除旧权限
        $delBool = DB::table('user_power')->where('user_id', '=', $request['uId'])->delete();

        $relt = [
            'sqlBool' => $delBool
        ];
        if (!$delBool) {
            return self::result($relt, -1, 'err_sql');
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

        $relt = [
            'sqlBool' => $bool
        ];
//        $data = DB::table('shop')->whereRaw('shop_id = ? and shop_name = ?', [1, 'aaa'])->get()->toArray();
        return self::result($relt);
    }

    // 新增商店
    public function addShop(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        if (!$request['shopName']) {
            return self::result([],-1, 'err_param');
        }

        $shop = Shop::create([
            'shop_name' => $request['shopName'],
            'company_name' => $request['companyName'],
            'remarks' => $request['remarks'],
            'create_user' => $data['user_id'],
            'update_user' => $data['user_id'],
        ]);

        $relt = [
            'shop' => $shop,
        ];
        return self::result($relt);
    }

    // 编辑店铺
    public function updateShop(Request $request)
    {
        $token = $request->header('Authorization');
        $token = $request['token'];
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        if (!$request['shop_id'] || !$request['shopName']) {
            return self::result([],-1, 'err_param');
        }

        $num = Shop::where('shop_id', '=' , $request['shop_id'])->update([
            'shop_name' => $request['shopName'],
            'company_name' => $request['companyName'],
            'remarks' => $request['remarks'],
            'update_user' => $data['user_id'],
        ]);

        $relt = [
            'sqlNum' => $num,
        ];
        return self::result($relt);
    }

    // 删除店铺
    public function delShop(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        if (!$request['shop_id']) {
            return self::result([],-1, 'err_param');
        }

        $num = Shop::destroy($request['shop_id']);

        $relt = [
            'delNum' => $num,
        ];

        Log::info('aaa', $relt);
        return self::result($relt);
    }

    // 获取所有用户
    public function getUsers(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        $data = User::all()->toArray();

        $relt = [
            'users' => $data,
        ];
        return self::result($relt);
    }

    // 获取所有商店
    public function getShops(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        $data = Shop::all()->toArray();

        $relt = [
            'shops' => $data,
        ];
        return self::result($relt);
    }

    // 店铺选择
    public function selectShop(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        if (!$request['shop_id']) {
            return self::result([], -1, 'err_param');
        }

        $bool = DB::table('users')->where('user_id', '=', $data['uId'])->update(['shop_id' => $request['shop_id']]);

        $relt = [
            'sqlBool' => $bool,
        ];

        return self::result($relt);
    }

}
