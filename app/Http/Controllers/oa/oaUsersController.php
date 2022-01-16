<?php

namespace App\Http\Controllers\oa;

use App\Http\Controllers\Controller;
use App\Libs\TokenSsl;
use App\Models\Role;
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

    // 修改密码
    public function changePwd(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        if (!$request['password']) {
            return self::result([], -1, 'err_param');
        }

        $bool = DB::table('users')->where('user_id', '=', $data['user_id'])->update(['password' => bcrypt($request['password'])]);

        $relt = [
            'success' => $bool
        ];
        return self::result($relt);
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

        $selectedId = $userDB->shop_id;
        if (!$userDB->shop_id) {
            $shop = Shop::find(1);
            $selectedId = $shop['shop_id'];
        }

        $relt = [
            'count' => $count,
            'permission' => $formatUserPowers,
//            'password' => decrypt($userDB->password),
            'userId' => $userDB->user_id,
            'username' => $userDB->username,
            'realName' => $userDB->nickname,
            'selectedShop' => $selectedId,
        ];
        return self::result($relt);
    }

    public function powerFormat($datas)
    {
        $formatData = [];
        foreach ($datas as $data) {
            $item['menu'] = json_decode($data->menu);
            $item['shop'] = $data->shop_id;
            $item['userId'] = $data->user_id;
            // 店铺
            $shop = Shop::find($data->shop_id);
            if (!$shop) {
                continue;
            }
            $item['shopName'] = $shop['shop_name'];

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
            'success' => $bool
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
            'success' => $bool
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
            'success' => $delBool
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
            'success' => $bool
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

        $relt = $shop;
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
            'changeNum' => $num,
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

        $relt = $data;
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

        $relt = $data;
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

        $bool = DB::table('users')->where('user_id', '=', $data['user_id'])->update(['shop_id' => $request['shop_id']]);

        $relt = [
            'success' => $bool,
        ];

        return self::result($relt);
    }

    // 角色添加
    public function addRole(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        if (!$request['roleName']) {
            return self::result([],-1, 'err_param');
        }

        $shop = Role::create([
            'role_name' => $request['roleName'],
            'remarks' => $request['remarks'],
            'menu' => json_encode($request['menu'], true)
        ]);

        $relt = $shop;
        return self::result($relt);
    }

    // 角色删除
    public function delRole(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        if (!$request['roleId']) {
            return self::result([],-1, 'err_param');
        }

        $num = Role::destroy($request['roleId']);

        $relt = [
            'num' => $num
        ];
        return self::result($relt);
    }

    // 角色权限修改
    public function updateRolePower(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        if (!$request['roleId'] || !$request['menu']) {
            return self::result([],-1, 'err_param');
        }

        $num = Role::where('id', '=', $request['roleId'])->update(['menu' => json_encode($request['menu'], true)]);

        $relt = [
            'num' => $num
        ];
        return self::result($relt);
    }

    // 角色列表获取
    public function getRoleList(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        $data = Role::all()->toArray();

        $relt = $data;
        return self::result($relt);
    }

}
