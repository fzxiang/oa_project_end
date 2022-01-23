<?php

namespace App\Http\Controllers\oa;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use App\Models\Writer;
use http\Params;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class businessController extends Controller
{
    // 添加订单
    public function addOrder(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['orderInfo']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $shop = Shop::find($shopId);
        if (!$shop) {
            return oaUsersController::result([],-1, 'no_shop');
        }

        $orderInfo = [
            'order' => [
                'aliOrder'          => '192103299124', // 淘宝订单编号
                'invoice'           => 'Zw24352', // 发单号
                'memberName'        => '呢大衣', // 会员名
                'taobaoPrice'       => '44', // 淘宝价格
                'customerContact'   => 'zj132044', // 客户微信或QQ
                'orderOutline'      => '糟糕订单', // 订单概要
            ],
            'writer' => [
                [
                'writerNum'         => '15282383333', // 写手手机号
                'name'              => 'zix', // 写手名
                'writerPrice'       => '665', // 写手派单价
                'alipayAccount'     => '234453fe', // 写手支付宝
                'qqAccount'         => '323030ajfe', // 写手qq
                'wechatAccount'     => 'fowajie2323', // 写手微信
                'writerSituation'   => 0, // 写手情况(1：拖稿，2：失联，3:拒绝修改，4：态度差)
                'writerQuality'     => 0, // 写手质量(1：好，2：中，3：差)
                ],
                [
                'writerNum'         => '15282384444', // 写手手机号
                'name'              => 'liming', // 写手名
                'writerPrice'       => '323', // 写手派单价
                'alipayAccount'     => '234453fe', // 写手支付宝
                'qqAccount'         => '323030ajfe', // 写手qq
                'wechatAccount'     => 'fowajie2323', // 写手微信
                'writerSituation'   => 0, // 写手情况(1：拖稿，2：失联，3:拒绝修改，4：态度差)
                'writerQuality'     => 0, // 写手质量(1：好，2：中，3：差)
                ],
            ],
            'other' => [
                'remarks'           => '就佛啊文件佛啊疯哦弄安分阿福那饿啊发女挨饿发呢Ivan哦发你哦撒娇的决定把你饿啊v按哦饿哦啊v奥v哦扫v女哦啊我弄', // 备注
            ]
        ];

        $orderInfo = $request['orderInfo'];

        // 添加订单数据
        $orderData = $orderInfo['order'];
        if (empty($orderData) || !$orderData['invoice'] || !$orderData['aliOrder']) {
            return oaUsersController::result([],-1, 'err_param');
        }
        $order = Order::create([
            'shop_id'           => $shopId,
            'acceptUser'        => $data['user_id'],
            'aliOrder'          => $orderData['aliOrder'],
            'invoice'           => $orderData['invoice'],
            'memberName'        => $orderData['memberName'] ?? '',
            'taobaoPrice'       => $orderData['taobaoPrice'] ?: 0,
            'customerContact'   => $orderData['customerContact'] ?? '',
            'orderOutline'      => $orderData['orderOutline'] ?? '',
            'remarks'           => $orderInfo['other']['remarks'] ?? '',
        ]);

        // 添加写手数据
        $writerData = $orderInfo['writer'];
        if (!empty($writerData)) {
            $writerOrderArr = [];
            foreach ($writerData as $item) {
                $writer = Writer::create([
                    'shop_id'           => $shopId,
                    'writerNum'         => $item['writerNum'] ?: 0,
                    'name'              => $item['name'] ?? '',
                    'alipayAccount'     => $item['alipayAccount'] ?? '',
                    'qqAccount'         => $item['qqAccount'] ?? '',
                    'wechatAccount'     => $item['wechatAccount'] ?? '',
                    'writerSituation'   => $item['writerSituation'] ?? '',
                    'writerQuality'     => $item['writerQuality'] ?? '',
                ]);

                $writerOrderArr[] = [
                    'shop_id'           => $shopId,
                    'writerId' => $writer['id'],
                    'orderId' => $order['id'],
                    'writerPrice' => $item['writerPrice'] ?: 0,
                ];
            }

            // 添加写手对应订单信息
            $writerOrderArr && $bool = DB::table('writer_order')->insert($writerOrderArr);
        }

        return oaUsersController::result();
    }

    // 编辑订单
    public function updateOrder(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['orderInfo']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $orderInfo = [
            'order' => [
                'id'                => 5,
                'aliOrder'          => '282103299149', // 淘宝订单编号
                'invoice'           => 'ZY293948', // 发单号
                'memberName'        => '即大奖哦', // 会员名
                'taobaoPrice'       => '172', // 淘宝价格
                'customerContact'   => 'zj132034', // 客户微信或QQ
                'orderOutline'      => '垃圾订单', // 订单概要
            ],
            'writer' => [
                [
                    'id'                => 1,
                    'writerNum'         => '15282445555', // 写手手机号
                    'name'              => 'fasf', // 写手名
                    'writerPrice'       => '238', // 写手派单价
                    'alipayAccount'     => '234453fe', // 写手支付宝
                    'qqAccount'         => '323030ajfe', // 写手qq
                    'wechatAccount'     => 'fowajie2323', // 写手微信
                    'writerSituation'   => 0, // 写手情况(1：拖稿，2：失联，3:拒绝修改，4：态度差)
                    'writerQuality'     => 0, // 写手质量(1：好，2：中，3：差)
                ],
                [
                    'id'                => 2,
                    'writerNum'         => '15282386666', // 写手手机号
                    'name'              => 'fasf', // 写手名
                    'writerPrice'       => '333', // 写手派单价
                    'alipayAccount'     => '234453fe', // 写手支付宝
                    'qqAccount'         => '323030ajfe', // 写手qq
                    'wechatAccount'     => 'fowajie2323', // 写手微信
                    'writerSituation'   => 0, // 写手情况(1：拖稿，2：失联，3:拒绝修改，4：态度差)
                    'writerQuality'     => 0, // 写手质量(1：好，2：中，3：差)
                ],
            ],
            'other' => [
                'remarks'           => '次擦你到家发哦嗯发哦违法哦饿哦啊减肥', // 备注
            ]
        ];

        $orderInfo = $request['orderInfo'];

        // 添加订单数据
        $orderData = $orderInfo['order'];
        if (empty($orderData) || !$orderData['id'] || !$orderData['invoice'] || !$orderData['aliOrder']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        // 更新订单信息
        $num = Order::where('id', '=', $orderData['id'])->update([
            'aliOrder'          => $orderData['aliOrder'],
            'invoice'           => $orderData['invoice'],
            'memberName'        => $orderData['memberName'] ?? '',
            'taobaoPrice'       => $orderData['taobaoPrice'] ?: 0,
            'customerContact'   => $orderData['customerContact'] ?? '',
            'orderOutline'      => $orderData['orderOutline'] ?? '',
            'remarks'           => $orderInfo['other']['remarks'] ?? '',
        ]);

        // 清空该订单写手信息
        $delNum = DB::table('writer_order')->where('orderId', '=', $orderData['id'])
            ->where('shop_id', '=', $shopId)->delete();

        // 更新写手信息
        $writerData = $orderInfo['writer'];
        if (!empty($writerData)) {
            $writerOrderArr = [];
            foreach ($writerData as $item) {
                if (!$item['id']) {
                    $writer = Writer::create([
                        'writerNum'         => $item['writerNum'] ?: 0,
                        'name'              => $item['name'] ?? '',
                        'alipayAccount'     => $item['alipayAccount'] ?? '',
                        'qqAccount'         => $item['qqAccount'] ?? '',
                        'wechatAccount'     => $item['wechatAccount'] ?? '',
                        'writerSituation'   => $item['writerSituation'] ?? '',
                        'writerQuality'     => $item['writerQuality'] ?? '',
                    ]);

                    $writerOrderArr[] = [
                        'writerId' => $writer['id'],
                        'orderId' => $orderData['id'],
                        'writerPrice' => $item['writerPrice'] ?: 0,
                    ];
                }
                else{
                    $num = Writer::where('id', '=', $item['id'])->update([
                        'writerNum'         => $item['writerNum'] ?: 0,
                        'name'              => $item['name'] ?? '',
                        'alipayAccount'     => $item['alipayAccount'] ?? '',
                        'qqAccount'         => $item['qqAccount'] ?? '',
                        'wechatAccount'     => $item['wechatAccount'] ?? '',
                        'writerSituation'   => $item['writerSituation'] ?? '',
                        'writerQuality'     => $item['writerQuality'] ?? '',
                    ]);

                    $writerOrderArr[] = [
                        'shop_id'           => $shopId,
                        'writerId' => $item['id'],
                        'orderId' => $orderData['id'],
                        'writerPrice' => $item['writerPrice'] ?: 0,
                    ];
                }

            }

            // 添加写手对应订单信息
            $writerOrderArr && $bool = DB::table('writer_order')->insert($writerOrderArr);
        }

        return oaUsersController::result();
    }

    // 检验写手手机号是否合规
    public function checkWriter(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        if (!$request['writerNum']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $mobile = $request['writerNum'];
        if (!preg_match("/^1[34578]\d{9}$/", $mobile)) {
            return oaUsersController::result([],-1, 'err_number');
        }

        return oaUsersController::result();
    }

    // 校验订单编号
    public function checkOrder(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        // 订单编号唯一
        if (!$request['aliOrder']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $order = DB::table('order')->where('aliOrder', '=', $request['aliOrder'])->get()->toArray();
        if (!empty($order)) {
            return oaUsersController::result([],0, 'repeated_Num');
        }

        return oaUsersController::result();
    }

    // 上传附件
    public function uploadOrderFile(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['type']) {
            return oaUsersController::result([],-1, 'err_type');
        }

        if (empty($request['fileData'])) {
            return oaUsersController::result([],-1, 'no_data');
        }

        $failData = [];
        switch ($request['type']) {
            case 1:
                // 上传总览附件
                $failData = $this->overviewData($request['fileData'], $shopId);
                break;
            case 2:
                // 上传退款附件
                $failData = $this->refundData($request['fileData'], $shopId);
                break;
        }

        return oaUsersController::result($failData);
    }

    // 上传总览附件
    private function overviewData($datas, $shopId)
    {
//        $datas = [
//            [
//                'aliOrder' => '282103299149', // 订单编号
//                'paymentMer' => 100, // 打款商家金额
//                'confirmTime' => strtotime("2022-01-01 12:00:00"), // 确认收货时间
//                'paymentTime' => strtotime("2022-01-02 14:00:00"), // 确认付款时间
//            ],
//            [
//                'aliOrder' => '192103299124', // 订单编号
//                'paymentMer' => 50, // 打款商家金额
//                'confirmTime' => strtotime("2022-01-03 08:00:00"), // 确认收货时间
//                'paymentTime' => strtotime("2022-01-04 15:00:00"), // 确认付款时间
//            ],
//            [
//                'aliOrder' => '14700238239489', // 订单编号
//                'paymentMer' => 0, // 打款商家金额
//                'confirmTime' => strtotime("2022-01-05 08:00:00"), // 确认收货时间
//                'paymentTime' => strtotime("2022-01-06 08:00:00"), // 确认付款时间
//            ],
//        ];

        $failData = [];
        // 上传附件数据处理
        foreach ($datas as $data) {
            $order = Order::where([['aliOrder', '=', $data['aliOrder']], ['shop_id', '=', $shopId]])->get()->toArray();
            if (!$order) {
                $failData[] = $data['aliOrder'];
            }

            // 更新数据
            $num = Order::where([['aliOrder', '=', $data['aliOrder']], ['shop_id', '=', $shopId]])->update([
                'taobaoPrice' => $data['paymentMer'],
                'paymentTime' => $data['paymentTime'],
                'receivingTime' => $data['confirmTime'],
                'overviewFilePrice' => $data['paymentMer'],
            ]);
        }

        return $failData;
    }

    // 上传退款附件
    private function refundData($datas, $shopId)
    {
//        $datas = [
//            [
//                'aliOrder' => '282103299149', // 订单编号
//                'refundState' => '退款成功', // 退款状态
//                'refundMod' => '售后退款', // 售中或售后
//                'actualPayment' => 30, // 买家实际支付金额
//                'refundMoney' => 30, // 买家退款金额
//            ],
//            [
//                'aliOrder' => '192103299124', // 订单编号
//                'refundState' => '退款失败', // 退款状态
//                'refundMod' => '售中退款', // 售中或售后
//                'actualPayment' => 40, // 买家实际支付金额
//                'refundMoney' => 0, // 买家退款金额
//            ],
//            [
//                'aliOrder' => '14700238239489', // 订单编号
//                'refundState' => '退款成功', // 退款状态
//                'refundMod' => '售后退款', // 售中或售后
//                'actualPayment' => 778, // 买家实际支付金额
//                'refundMoney' => 938, // 买家退款金额
//            ],
//        ];

        $failData = [];
        foreach ($datas as $k => $data) {
            if ($data['refundState'] != '退款成功') {
                continue;
            }

            if ($data['refundMod'] != '售后退款') {
                continue;
            }

            // 获取打款商家金额
            $order = Order::where('aliOrder', '=', $data['aliOrder'])->get()->toArray();
            if (!$order) {
                $failData[] = $data['aliOrder'];
                continue;
            }

            $overviewFilePrice = $order[0]['overviewFilePrice'] ?: 0;

            if ($overviewFilePrice < $data['refundMoney']) {
                $failData[] = $data['aliOrder'];
                continue;
            }

            $taobaoPrice = $overviewFilePrice - $data['refundMoney'];
            $num = Order::where([['aliOrder', '=', $data['aliOrder']], ['shop_id', '=', $shopId]])->update([
                'taobaoPrice' => $taobaoPrice,
            ]);
        }
        return $failData;
    }


    // 我的订单导出
    public function exportOrder(Request $request)
    {
        // 导出字断
        $fileField = [
            '店铺名称', '客服ID', '接单客服', '淘宝订单号', '会员名', '打款商家金额', '买家退款金额', '买家实际支付金额',
            '总表(打款商家金额)-退款表(买家退款金额)', "客服填写价格", "最终对比价格", "订单概要", "订单付款时间", "确认收货时间",
            '写手名 01', '写手QQ 01', '写手手机号 01', '写手派单价 01', '写手支付宝 01',
            '写手名 02', '写手QQ 02', '写手手机号 02', '写手派单价 02', '写手支付宝 02',
            '写手名 03', '写手QQ 03', '写手手机号 03', '写手派单价 03', '写手支付宝 03',
        ];


    }

    // 我的订单检索
    public function searchOrder(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $data = [
            'page' => 1, // 第几页
            'pageSize' => 10, // 一页几条数据
            'aliOrder' => '', // 淘宝订单编号
            'invoice' => '', // 发单号
            'memberName' => '', // 会员名
            'settleState' => 0, // 结算状态(1:已结算，2:未结算, 3:暂缓结算)
            'pStartTime' => 0, // 订单付款开始时间
            'pEndTime' => 0, // 订单付款结束时间
            'rStartTime' => 0, // 订单收货开始时间
            'rEndTime' => 0, // 订单收货结束时间
        ];

        $data = $request['searchParams'];

        // 页码，条数
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $limit = isset($data['pageSize']) ? intval($data['pageSize']) : 10;
        $page  = max(1, $page);

        // 跳过的条数
        $offset = $limit * ($page - 1);

        // sql语句处理
        $sqlArr = [];
        // 默认开始时间
        if (isset($data['pStartTime']) && $data['pStartTime']) {
            $pStartTime = strtotime($data['pStartTime']);
        }
        else {
            $pStartTime = strtotime("2022-01-01");
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
            $pEndTime = time();
        }

        $sqlArr[] = ['shop_id', '=', $shopId];
        $sqlArr[] = ['paymentTime', '>=', $pStartTime];
        $sqlArr[] = ['paymentTime', '<=', $pEndTime];

        // 确认收货时间
        if (isset($data['rStartTime']) && $data['rStartTime'] && isset($data['rEndTime']) && $data['rEndTime']) {
            $sqlArr[] = ['receivingTime', '>=', $data['rStartTime']];
            $sqlArr[] = ['receivingTime', '<=', $data['rEndTime']];
        }

        // 淘宝订单编号
        if (isset($data['aliOrder']) && $data['aliOrder']) {
            $sqlArr[] = ['aliOrder', '=', $data['aliOrder']];
        }

        // 发单号
        if (isset($data['invoice']) && $data['invoice']) {
            $sqlArr[] = ['invoice', '=', $data['invoice']];
        }

        // 会员名
        if (isset($data['memberName']) && $data['memberName']) {
            $sqlArr[] = ['memberName', '=', $data['memberName']];
        }

        // 结算状态
        if (isset($data['settleState']) && $data['settleState']) {
            $sqlArr[] = ['settleState', '=', $data['settleState']];
        }

        $order = Order::where($sqlArr)->skip($offset)->take($limit)->get()->toArray();

        $relt = $order;
        return oaUsersController::result($relt);
    }

    // 写手对应订单补偿状态
    public function updateOrderRedress(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        if (!$request['orderId'] || !$request['writerId']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $state = intval($request['state']);
        $num = DB::table('writer_order')->where('writerId', '=', $request['writerId'])
            ->where('orderId', '=', $request['orderId'])
            ->update([
                'compensateState' => $state,
            ]);

        return oaUsersController::result($num);
    }

    // 写手总览检索
    public function searchWriter(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $data = [
            'page' => 1, // 第几页
            'pageSize' => 10, // 一页几条数据
            'writerNum' => '', // 写手手机号
            'qqAccount' => '', // 写手qq
            'wechatAccount' => '', // 写手微信
        ];

        $data = $request['searchParams'];

        // 页码，条数
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $limit = isset($data['pageSize']) ? intval($data['pageSize']) : 10;
        $page  = max(1, $page);

        // 跳过的条数
        $offset = $limit * ($page - 1);

        $sqlArr = [];

        // 写手商店
        $sqlArr[] = ['shop_id', '=', $shopId];

        // 写手手机号
        if (isset($data['writerNum']) && $data['writerNum']) {
            $sqlArr[] = ['writerNum', '=', $data['writerNum']];
        }

        // 写手qq
        if (isset($data['qqAccount']) && $data['qqAccount']) {
            $sqlArr[] = ['qqAccount', '=', $data['qqAccount']];
        }

        // 写手微信
        if (isset($data['wechatAccount']) && $data['wechatAccount']) {
            $sqlArr[] = ['wechatAccount', '=', $data['wechatAccount']];
        }

        $writer = Writer::where($sqlArr)->skip($offset)->take($limit)->get()->toArray();

        $relt = $writer;
        return oaUsersController::result($relt);
    }

    // 写手信息编辑
    public function updateWriter(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['id'] || !$request['writerInfo']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $data = [
            'name' => 'dadan', // 写手名
            'qqAccount' => '10929323', // 写手qq
            'wechatAccount' => 'aj293293', // 写手微信
            'alipayAccount' => '102932934@qq.com', // 写手支付宝
        ];

        $data = $request['writerInfo'];

        $num = Writer::where([['id', '=', $request['id']], ['shop_id', '=', $shopId]])->update([
            'name' => $data['name'],
            'qqAccount' => $data['qqAccount'],
            'wechatAccount' => $data['wechatAccount'],
            'alipayAccount' => $data['alipayAccount'],
        ]);

        return oaUsersController::result($num);
    }

    // 写手报表检索
    public function searchWriterOfKefu(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $data = [
            'page' => 1, // 第几页
            'pageSize' => 10, // 一页几条数据
            'writerNum' => '140294402340', // 写手手机号
            'qqAccount' => '109284929@qq.com', // 写手qq号
            'wechatAccount' => 'zy239301', // 写手微信号
            'writerId' => 1, // 写手ID
            'settleState' => 1, // 结算状态(1:已结算，2:未结算, 3:暂缓结算)
            'pStartTime' => 0, // 订单付款开始时间
            'pEndTime' => 0, // 订单付款结束时间
            'rStartTime' => 0, // 订单收货开始时间
            'rEndTime' => 0, // 订单收货结束时间
        ];

        $data = $request['searchParams'];

        // 页码，条数
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $limit = isset($data['pageSize']) ? intval($data['pageSize']) : 10;
        $page  = max(1, $page);

        // 跳过的条数
        $offset = $limit * ($page - 1);

        $writerSqlArr = [];
        $writerSqlArr[] = ['shop_id', '=', $shopId];
        // 写手手机号
        if (isset($data['writerNum']) && $data['writerNum']) {
            $writerSqlArr[] = ['writerNum', '=', $data['writerNum']];
        }

        // 写手qq
        if (isset($data['qqAccount']) && $data['qqAccount']) {
            $writerSqlArr[] = ['qqAccount', '=', $data['qqAccount']];
        }

        // 写手微信
        if (isset($data['wechatAccount']) && $data['wechatAccount']) {
            $writerSqlArr[] = ['wechatAccount', '=', $data['wechatAccount']];
        }

        // 写手id
        if (isset($data['writerId']) && $data['writerId']) {
            $writerSqlArr[] = ['writerId', '=', $data['writerId']];
        }

        $writer = Writer::where($writerSqlArr)->skip($offset)->take($limit)->get()->toArray();

        // 获取对应写手所有订单ID
        $jsonInfo = [];
        $writerIds = [];
        foreach ($writer as $item) {
            $writerIds[] = $item['id'];
            $jsonInfo[$item['id']] = [
                'id' => $item['id'],
                'writerNum' => $item['writerNum'],
                'name' => $item['name'],
                'alipayAccount' => $item['alipayAccount'],
                'qqAccount' => $item['qqAccount'],
                'wechatAccount' => $item['wechatAccount'],
                'writerSituation' => $item['writerSituation'],
                'writerQuality' => $item['writerQuality'],
                'childOrder' => [],
            ];
        }
        $writerOrders = DB::table('writer_order')->whereIn('writerId', $writerIds)->get()->toArray();

        // 默认开始时间
        if (isset($data['pStartTime']) && $data['pStartTime']) {
            $pStartTime = strtotime($data['pStartTime']);
        }
        else {
            $pStartTime = strtotime("2022-01-01");
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
            $pEndTime = time();
        }

        // 确认收货时间
        $rStartTime = $rEndTime = 0;
        if (isset($data['rStartTime']) && isset($data['rEndTime'])) {
            $rStartTime = strtotime($data['rStartTime']);
            $rEndTime = strtotime($data['rEndTime']);
        }

        $settleState = $data['settleState'] ?? 0;
        // 符合条件数据拼接
        foreach ($writerOrders as $item) {
            // 遍历查询订单
            $order = Order::find($item->orderId);

            // 状态不符合
            if ($settleState && $settleState != $order['settleState']) {
                continue;
            }

            // 付款时间不符合
            if ($order['paymentTime'] < $pStartTime || $order['paymentTime'] > $pEndTime) {
                continue;
            }

            // 收货时间不符合
            if (($rStartTime && $order['receivingTime'] < $rStartTime) || ($rEndTime && $order['receivingTime'] > $rEndTime)) {
                continue;
            }

            // 符合条件订单
            if (!array_key_exists($item->writerId, $jsonInfo)) {
                continue;
            }

            $jsonInfo[$item->writerId]['childOrder'][] = [
                'id' => $order['id'],
                'invoice' => $order['invoice'],
                'acceptUser' => $order['acceptUser'],
                'aliOrder' => $order['aliOrder'],
                'settleState' => $order['settleState'],
                'taobaoPrice' => $order['taobaoPrice'],
                'paymentTime' => $order['paymentTime'],
                'receivingTime' => $order['receivingTime'],
            ];
        }

        return oaUsersController::result($jsonInfo);
    }

    // 写手报表上传已结算订单
    public function uploadSettled(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (empty($request['fileData'])) {
            return oaUsersController::result([],-1, 'no_data');
        }

        $data = [
            [
                'alipayAccount' => '1302942304', // 收款方支付宝账号
                'name' => '张静', // 收款方姓名
                'price' => 43, // 金额
                'invoice' => 'jx-zn1213', // 单号
            ],
            [
                'alipayAccount' => '1869239322', // 收款方支付宝账号
                'name' => '张静', // 收款方姓名
                'price' => 25, // 金额
                'invoice' => 'jx-zn1213', // 单号
            ],
            [
                'alipayAccount' => '1524534532', // 收款方支付宝账号
                'name' => '张静', // 收款方姓名
                'price' => 546, // 金额
                'invoice' => 'jx-zn1213', // 单号
            ],
        ];

    }

    // 写手报表订单导出
    public function exportWriter(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $fileField = [
            '序号', '收款方支付宝账号', '收款方姓名', '金额', '备注',
        ];

    }

    // 写手报表订单全部结算
    public function quickWriterOrderStatus(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['writerId'] || !$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $data = [
            'settleState' => 0, // 结算状态(1:已结算，2:未结算, 3:暂缓结算)
            'pStartTime' => 0, // 订单付款开始时间
            'pEndTime' => 0, // 订单付款结束时间
            'rStartTime' => 0, // 订单收货开始时间
            'rEndTime' => 0, // 订单收货结束时间
        ];

        $data = $request['searchParams'];

        // sql语句处理
        // 默认开始时间
        if (isset($data['pStartTime']) && $data['pStartTime']) {
            $pStartTime = strtotime($data['pStartTime']);
        }
        else {
            $pStartTime = strtotime("2022-01-01");
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
            $pEndTime = time();
        }

        // 确认收货时间
        $rStartTime = $rEndTime = 0;
        if (isset($data['rStartTime']) && isset($data['rEndTime'])) {
            $rStartTime = strtotime($data['rStartTime']);
            $rEndTime = strtotime($data['rEndTime']);
        }

        // 查询当前写手所有订单ID
        $writerOrders = DB::table('writer_order')->where('writerId', '=', $request['writerId'])
            ->where('shop_id', '=', $shopId)
            ->get()->toArray();

        $settleState = $data['settleState'] ?? 0;
        foreach ($writerOrders as $item) {
            // 遍历查询订单
            $order = Order::find($item->orderId);

            // 状态不符合
            if ($settleState && $settleState != $order['settleState']) {
                continue;
            }

            // 付款时间不符合
            if ($order['paymentTime'] < $pStartTime || $order['paymentTime'] > $pEndTime) {
                continue;
            }

            // 收货时间不符合
            if (($rStartTime && $order['receivingTime'] < $rStartTime) || ($rEndTime && $order['receivingTime'] > $rEndTime)) {
                continue;
            }

            $order['settleState'] = 1;
            $order->save();

            // 写手对应订单结算状态
            $num = DB::table('writer_order')->where('writerId', '=', $item->writerId)
                ->where('orderId', '=', $item->orderId)
                ->update([
                    'wSettleState' => 1,
                ]);
        }

        return oaUsersController::result();
    }

    // 客服报表检索
    public function searchCustomer(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $data = [
            'page' => 1, // 第几页
            'pageSize' => 10, // 一页几条数据
            'customerId' => 15, // 客服ID
            'settleState' => 0, // 结算状态(1:已结算，2:未结算, 3:暂缓结算)
            'pStartTime' => 0, // 订单付款开始时间
            'pEndTime' => 0, // 订单付款结束时间
            'rStartTime' => 0, // 订单收货开始时间
            'rEndTime' => 0, // 订单收货结束时间
        ];

        $data = $request['searchParams'];

        $relt = $this->kefuCheckSearch($shopId, $data);

        return oaUsersController::result($relt);
    }

    private function kefuCheckSearch($shopId, $params)
    {
        $data = $params ?? [];
        if (empty($data)) {
            return [];
        }

        // 页码，条数
        $page = isset($data['page']) ? intval($data['page']) : 1;
        $limit = isset($data['pageSize']) ? intval($data['pageSize']) : 10;
        $page  = max(1, $page);

        // 跳过的条数
        $offset = $limit * ($page - 1);

        // sql语句处理
        $sqlArr = [];
        // 默认开始时间
        if (isset($data['pStartTime']) && $data['pStartTime']) {
            $pStartTime = strtotime($data['pStartTime']);
        }
        else {
            $pStartTime = strtotime("2022-01-01");
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
            $pEndTime = time();
        }

        $sqlArr[] = ['shop_id', '=', $shopId];
        $sqlArr[] = ['paymentTime', '>=', $pStartTime];
        $sqlArr[] = ['paymentTime', '<=', $pEndTime];

        // 确认收货时间
        if (isset($data['rStartTime']) && $data['rStartTime'] && isset($data['rEndTime']) && $data['rEndTime']) {
            $sqlArr[] = ['receivingTime', '>=', $data['rStartTime']];
            $sqlArr[] = ['receivingTime', '<=', $data['rEndTime']];
        }

        // 客服ID
        if (isset($data['customerId']) && $data['customerId']) {
            $sqlArr[] = ['acceptUser', '=', $data['customerId']];
        }

        // 结算状态
        if (isset($data['settleState']) && $data['settleState']) {
            $sqlArr[] = ['settleState', '=', $data['settleState']];
        }

        $order = Order::where($sqlArr)->skip($offset)->take($limit)->get()->toArray();

        return $order;
    }

    // 客服报表批量修改状态
    public function updateAllOrderState(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if ($request['state'] || !$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $data = [
            'page' => 1, // 第几页
            'pageSize' => 10, // 一页几条数据
            'customerId' => 15, // 客服ID
            'settleState' => 0, // 结算状态(1:已结算，2:未结算, 3:暂缓结算)
            'pStartTime' => 0, // 订单付款开始时间
            'pEndTime' => 0, // 订单付款结束时间
            'rStartTime' => 0, // 订单收货开始时间
            'rEndTime' => 0, // 订单收货结束时间
        ];

        $data = $request['searchParams'];

        $orders = $this->kefuCheckSearch($shopId, $data);
        $ids = array_column($orders, 'id');

        $num = Order::whereIn('id', $ids)->update([
            'settleState' => $request['state'],
        ]);

        return oaUsersController::result($num);
    }

    // 客服报表导出
    public function exportCustomer(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $fileField = [
            '发单号', '接单客服', '淘宝订单编号', '会员名', '淘宝价格', '写手派单价格', '结算状态', '订单付款时间', '确认收货时间',
        ];

    }

    // 更新单个单子状态
    public function updateOneOrderState(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        if (!$request['orderId'] || !$request['state']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $order = Order::find($request['orderId']);
        if (empty($order)) {
            oaUsersController::result([], -1, 'no_data');
        }

        $order['settleState'] = $request['state'];

        $order->save();

        return oaUsersController::result();
    }

    // 根据订单ID拉取写手信息
    public function getWritersOfOrder(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['orderId']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $orderWriters = DB::table('writer_order')->where('orderId', '=', $request['orderId'])
            ->where('shop_id', '=', $shopId)
            ->get()->toArray();

        $writerInfo = [];
        foreach ($orderWriters as $item) {
            $writer = Writer::find($item->writerId);
            if (empty($writer)) {
                continue;
            }

            $writerInfo[$item->writerId] = [
                'id' => $writer['id'],
                'writerNum' => $writer['writerNum'],
                'name' => $writer['name'],
                'alipayAccount' => $writer['alipayAccount'],
                'qqAccount' => $writer['qqAccount'],
                'wechatAccount' => $writer['wechatAccount'],
                'writerSituation' => $writer['writerSituation'],
                'writerQuality' => $writer['writerQuality'],
                'compensateState' => $item->compensateState,
            ];
        }

        return oaUsersController::result($writerInfo);
    }

    // 根据写手ID拉取订单信息
    public function getOrdersOfWriter(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        if (!$request['writerId']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $orderWriters = DB::table('writer_order')->where('orderId', '=', $request['orderId'])
            ->where('shop_id', '=', $shopId)
            ->get()->toArray();

        $orderInfo = [];
        foreach ($orderWriters as $item) {
            $order = Order::find($item->orderId);
            if (empty($order)) {
                continue;
            }

            $orderInfo[] = [
                'id' => $order['id'],
                'invoice' => $order['invoice'],
                'acceptUser' => $order['acceptUser'],
                'aliOrder' => $order['aliOrder'],
                'settleState' => $order['settleState'],
                'taobaoPrice' => $order['taobaoPrice'],
                'paymentTime' => $order['paymentTime'],
                'receivingTime' => $order['receivingTime'],
            ];
        }

        return oaUsersController::result($orderInfo);
    }

}
