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
            'password' => 'nullable|min:6'
        ]);

        $password = $request['password'];
        $user = User::create([
            'username' => $request['username'],
            'nickname' => $request['nickname'] ?? '',
            'password' => bcrypt($password),
            'role_id' => $request['role_id'] ?: 0,
        ]);

        // 权限处理
//        $time = date('Y-m-d H:i:s');
//        $arr = [];
//        $jsonConf = $request['powerJson'] ?? [];
//        $powerJson = $request['powerJson'] ? json_decode($jsonConf, true) : [];
//        if ($powerJson) {
//            foreach ($powerJson as $conf) {
//                $sqlArr = [
//                    'user_id' => $data['user_id'],
//                    'shop_id' => $conf['shopId'],
//                    'menu' => json_encode($conf['power'], true),
//                    'created_at' => $time,
//                    'updated_at' => $time,
//                ];
//                $arr[] = $sqlArr;
//            }
//        }
//
//        $arr && $bool = DB::table('user_power')->insert($arr);

        $relt = [
            'user' => $user,
        ];
        return self::result($relt);
    }

    // 编辑用户
    public function updateUser(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        if (!$request['user_id']) {
            return self::result([],-1, 'err_param');
        }

        $num = User::where('user_id', '=', $request['user_id'])->update([
            'username' => $request['username'],
            'nickname' => $request['nickname'] ?? '',
            'role_id' => $request['role_id'] ?: 0,
        ]);

        return self::result($num);
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

        if (!$request['shop_name']) {
            return self::result([],-1, 'err_param');
        }

        $shop = Shop::create([
            'shop_name' => $request['shop_name'],
            'company_name' => $request['company_name'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'create_user' => $data['user_id'] ?: 0,
            'update_user' => $data['user_id'] ?: 0,
        ]);

        $relt = $shop;
        return self::result($relt);
    }

    // 编辑店铺
    public function updateShop(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        // todo 权限管理

        if (!$request['shop_id'] || !$request['shop_name']) {
            return self::result([],-1, 'err_param');
        }

        $num = Shop::where('shop_id', '=' , $request['shop_id'])->update([
            'shop_name' => $request['shop_name'],
            'company_name' => $request['company_name'] ?? '',
            'remarks' => $request['remarks'] ?? '',
            'update_user' => $data['user_id'] ?: 0,
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

        return self::result($relt);
    }

    // 商店查询
    public function searchShop(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        if (!$request['shop_name']) {
            return self::result([],-1, 'err_param');
        }

        $shops = DB::table('shop')->where('shop_name', '=', $request['shop_name'])->get()->toArray();

        if (!$shops) {
            return self::result([], -1, 'no_shop');
        }

        $shop = $shops[0];
        $relt = $shop;
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

        foreach ($data as $k => $item) {
            $role = Role::find($item['role_id']);
            $data[$k]['role_name'] = $role['role_name'] ?? '';
        }

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

        if (!$request['role_name'] || !$request['role']) {
            return self::result([],-1, 'err_param');
        }

        $shop = Role::create([
            'role_name' => $request['role_name'],
            'remarks' => $request['remarks'] ?? '',
            'menu' => json_encode($request['menu'], true),
            'role' => $request['role'],
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

        if (!$request['id']) {
            return self::result([],-1, 'err_param');
        }

        $num = Role::destroy($request['id']);

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

        if (!$request['id'] || !$request['menu']) {
            return self::result([],-1, 'err_param');
        }

        $num = Role::where('id', '=', $request['id'])->update(['menu' => json_encode($request['menu'], true)]);

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

        foreach ($data as $k => $item) {
            $data[$k]['menu'] = json_decode($item['menu']);
        }

        $relt = $data;
        return self::result($relt);
    }

    // 获取客服下拉列表
    public function getUsersOfPower(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = self::getUserIdOfToken($token)) {
            return self::result([],-1, 'err_token');
        }

        if (!$request['shop_id']) {
            return self::result([],-1, 'err_param');
        }

        $kefuArr = [3,4,5];
        // 获取当前用户对象
        $users = User::all()->toArray();

        $dataInfo = [];
        foreach ($users as $user) {
            // 当前用户分配的角色ID
            $roleId = $user['role_id'] ?: 0;

            $isKefu = false;
            if ($roleId) {
                $roleArr = Role::find($roleId);
                $roleState = $roleArr['role'] ?: 0;
                in_array($roleState, $kefuArr) && $isKefu = true;
            }

            $userPowers = DB::table('user_power')->where('user_id', '=', $user['user_id'])->get()->toArray();

            // 该客服存在该商店权限
            $hasShop = false;
            foreach ($userPowers as $item) {
                if ($item->shop_id != $request['shop_id']) {
                    continue;
                }
                $hasShop = true;
                break;
            }

            if ($isKefu && $hasShop) {
                $dataInfo[] = [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                ];
            }
        }

        $relt = $dataInfo;
        return self::result($relt);
    }


    // 测试压包
    public function testGzencode(Request $request)
    {
        $arrJson = [
            'a' => 1,
            'b' => '猜猜我是谁',
            'c' => [2,1,34,523,4,23,4],
            'd' => [
                'aa' => 22,
                'bb' => 'balabala',
                'cc' => '发哦飞机哦啊我服'
            ]
        ];

        $jsonEn = json_encode($arrJson);
        $abc = gzencode($jsonEn, 9);
        $ddd = base64_encode($abc);
        $eee = serialize($arrJson);

        $relt = $eee;
        return self::result($relt);
    }

    // 测试导出
    public function exportTest(Request $request)
    {
        ini_set("memory_limit", "128M");    // 设置使用内存
        ini_set('max_execution_time', 120); // 强制设置PHP超时时间
        // 最大导出数量
        $count = 10000;

        // 导出的csv名称
        $title = '大道质检ofaoejoa';
        $title = urlencode($title); // 转码中文，防止乱码 需要前端将文件名进行解码

        header('Content-Type: application/vnd.ms-excel');   //header设置
        header("Content-Disposition: attachment;filename=" . $title . ".csv");
//        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header('Cache-Control: max-age=0');

        $fp = fopen('php://output', 'a');//打开output流logGetList
        if (ob_get_length() > 0) {
            ob_clean();
        }

        // 获取表头数据
        $headings = ['a1', 'a2', 'a3', 'a4'];

        mb_convert_variables('GBK', 'UTF-8', $headings);
        fputcsv($fp, $headings);

        $maps = ['b1', 'b2', 'b3', 'b4'];

        $limit   = 1000;          // 分片请求限制查询数量
        $pageAll = ceil($count / $limit); // 总页数

        for ($page = 1; $page <= $pageAll; $page++) {

            // 获取数据
            $dataList = [
                'count' => 10,
                'data' => [
                    [
                    'b1' => 100,
                    'b2' => 'jfoajeoa',
                    'b3' => '哈佛啊饿哦',
                    'b4' => 2000
                    ],
                    [
                    'b1' => 232,
                    'b2' => '大法',
                    'b3' => 'safeaf',
                    'b4' => 333
                    ],
                ]
            ];

            if (isset($dataList['count']) && (int)$dataList['count'] <= $count) {
                $pageAll = ceil((int)$dataList['count'] / $limit); // 总页数
            }

            $data = $dataList['data'] ?? [];

            foreach ($data as $val) {
                $newData = [];
                foreach ($maps as $v) {
                    $new_value = '';
                    if (isset($val[$v])) {
                        #判断返回格式
                        if (is_int($val[$v]) || is_string($val[$v])) {
                            #防止科学计数法
                            $new_value = (string)$val[$v] . ($this->checkScientific($val[$v]) ? "\t" : '');
                        } else {
                            #非int类型 强转，主要是防止数组格式
                            $new_value = strval($val[$v]);
                        }
                        $newData[] = $new_value;
                    } else {
                        $newData[] = $new_value;
                    }
                }
                mb_convert_variables('GBK', 'UTF-8', $newData);
                fputcsv($fp, $newData);
                unset($newData);
            }
            // 销毁变量，节省内存空间
            unset($data, $dataList);
        }
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        fclose($fp);
        exit();
    }

    public function checkScientific($str)
    {
        if (strstr($str, ',')) {
            $str = explode(',', $str)[0];
        }
        if (is_numeric($str) && mb_strlen((string)$str) > 10) {
            return true;
        } else {
            return false;
        }

    }

}
