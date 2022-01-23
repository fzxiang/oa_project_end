<?php

namespace App\Http\Controllers\oa;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Writer;
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

        if (!$request['orderInfo']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $shopId = 1;

        $orderInfo = [
            'order' => [
                'aliOrder'          => '192103299123', // 淘宝订单编号
                'invoice'           => 'ZY23JAJ', // 发单号
                'memberName'        => '即大奖哦', // 会员名
                'taobaoPrice'       => '172', // 淘宝价格
                'customerContact'   => 'zj132034', // 客户微信或QQ
                'orderOutline'      => '垃圾订单', // 订单概要
            ],
            'writer' => [
                [
                'writerNum'         => '15282383293', // 写手手机号
                'name'              => 'fasf', // 写手名
                'writerPrice'       => '238', // 写手派单价
                'alipayAccount'     => '234453fe', // 写手支付宝
                'qqAccount'         => '323030ajfe', // 写手qq
                'wechatAccount'     => 'fowajie2323', // 写手微信
                'writerSituation'   => 0, // 写手情况(1：拖稿，2：失联，3:拒绝修改，4：态度差)
                'writerQuality'     => 0, // 写手质量(1：好，2：中，3：差)
                ],
                [
                'writerNum'         => '15282383293', // 写手手机号
                'name'              => 'fasf', // 写手名
                'writerPrice'       => '238', // 写手派单价
                'alipayAccount'     => '234453fe', // 写手支付宝
                'qqAccount'         => '323030ajfe', // 写手qq
                'wechatAccount'     => 'fowajie2323', // 写手微信
                'writerSituation'   => 0, // 写手情况(1：拖稿，2：失联，3:拒绝修改，4：态度差)
                'writerQuality'     => 0, // 写手质量(1：好，2：中，3：差)
                ],
            ],
            'other' => [
                'remarks'           => '就佛啊文件佛啊', // 备注
            ]
        ];

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

        if (!$request['orderInfo']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $shopId = 1;

        $orderInfo = [
            'order' => [
                'id'                => 1,
                'aliOrder'          => '192103299123', // 淘宝订单编号
                'invoice'           => 'ZY23JAJ', // 发单号
                'memberName'        => '即大奖哦', // 会员名
                'taobaoPrice'       => '172', // 淘宝价格
                'customerContact'   => 'zj132034', // 客户微信或QQ
                'orderOutline'      => '垃圾订单', // 订单概要
            ],
            'writer' => [
                [
                    'id'                => 1,
                    'writerNum'         => '15282383293', // 写手手机号
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
                    'writerNum'         => '15282383293', // 写手手机号
                    'name'              => 'fasf', // 写手名
                    'writerPrice'       => '238', // 写手派单价
                    'alipayAccount'     => '234453fe', // 写手支付宝
                    'qqAccount'         => '323030ajfe', // 写手qq
                    'wechatAccount'     => 'fowajie2323', // 写手微信
                    'writerSituation'   => 0, // 写手情况(1：拖稿，2：失联，3:拒绝修改，4：态度差)
                    'writerQuality'     => 0, // 写手质量(1：好，2：中，3：差)
                ],
            ],
            'other' => [
                'remarks'           => '就佛啊文件佛啊', // 备注
            ]
        ];

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

        if (!$request['type']) {
            return oaUsersController::result([],-1, 'err_type');
        }

        if (empty($request['fileData'])) {
            return oaUsersController::result([],-1, 'no_data');
        }

        $shopId = 1;

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
        $datas = [
            [
                'aliOrder' => '14700238239423', // 订单编号
                'paymentMer' => 100, // 打款商家金额
                'confirmTime' => '23424', // 确认收货时间
                'paymentTime' => '435802', // 确认付款时间
            ],
            [
                'aliOrder' => '1470023824304', // 订单编号
                'paymentMer' => 50, // 打款商家金额
                'confirmTime' => '567', // 确认收货时间
                'paymentTime' => '24345', // 确认付款时间
            ],
            [
                'aliOrder' => '14700238239489', // 订单编号
                'paymentMer' => 0, // 打款商家金额
                'confirmTime' => '14324', // 确认收货时间
                'paymentTime' => '8675802', // 确认付款时间
            ],
        ];

        $failData = [];
        // 上传附件数据处理
        foreach ($datas as $data) {
            // 更新数据
            $num = Order::where([['aliOrder', '=', $data['aliOrder'], ['shop_id', '=', $shopId]]])->update([
                'taobaoPrice' => $data['paymentMer'],
                'paymentTime' => $data['paymentTime'],
                'receivingTime' => $data['confirmTime'],
                'overviewFilePrice' => $data['paymentMer'],
            ]);

            if (!$num) {
                $failData[] = $data['aliOrder'];
            }
        }

        return $failData;
    }

    // 上传退款附件
    private function refundData($datas, $shopId)
    {
        $datas = [
            [
                'aliOrder' => '14700238239489', // 订单编号
                'refundState' => '退款成功', // 退款状态
                'refundMod' => '售中退款', // 售中或售后
                'actualPayment' => 120, // 买家实际支付金额
                'refundMoney' => 120, // 买家退款金额
            ],
            [
                'aliOrder' => '14700238239489', // 订单编号
                'refundState' => '退款失败', // 退款状态
                'refundMod' => '售中退款', // 售中或售后
                'actualPayment' => 382, // 买家实际支付金额
                'refundMoney' => 0, // 买家退款金额
            ],
            [
                'aliOrder' => '14700238239489', // 订单编号
                'refundState' => '退款成功', // 退款状态
                'refundMod' => '售后退款', // 售中或售后
                'actualPayment' => 778, // 买家实际支付金额
                'refundMoney' => 938, // 买家退款金额
            ],
        ];

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
            $overviewFilePrice = $order['overviewFilePrice'] ?: 0;

            if ($overviewFilePrice < $data['refundMoney']) {
                $failData[] = $data['aliOrder'];
                continue;
            }

            $taobaoPrice = $overviewFilePrice - $data['refundMoney'];
            $num = Order::where([['aliOrder', '=', $data['aliOrder']], [['shop_id', '=', $shopId]]])->update([
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

        if (!$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $shopId = 1;

        $data = [
            'pageNumber' => 1, // 第几页
            'pageSize' => 10, // 一页几条数据
            'aliOrder' => '140294402340', // 淘宝订单编号
            'invoice' => 'zy239301', // 发单号
            'memberName' => 'jiosjd', // 会员名
            'settleState' => 1, // 结算状态(1:已结算，2:未结算, 3:暂缓结算)
            'pStartDate' => 0, // 订单付款开始时间
            'pEndDate' => 0, // 订单付款结束时间
            'rStartDate' => 0, // 订单收货开始时间
            'rEndDate' => 0, // 订单收货结束时间
        ];

        // 页码，条数
        $page = isset($data['pageNumber']) ? intval($data['pageNumber']) : 1;
        $limit = isset($data['pageSize']) ? intval($data['pageSize']) : 10;
        $page  = max(1, $page);

        // 跳过的条数
        $offset = $limit * ($page - 1);

        // sql语句处理
        $sqlArr = [];
        // 默认开始时间
        if (isset($data['pStartData']) && $data['pStartData']) {
            $pStartTime = strtotime($data['pStartData']);
        }
        else {
            $pStartTime = strtotime("2022-01-01");
        }

        // 默认结束时间
        if (isset($data['pEndData']) && $data['pEndData']) {
            $pEndTime = strtotime($data['pEndData']);
        }
        else {
            $pEndTime = time();
        }

        $sqlArr[] = ['shop_id', '=', $shopId];
        $sqlArr[] = ['paymentTime', '>=', $pStartTime];
        $sqlArr[] = ['paymentTime', '<=', $pEndTime];

        // 确认收货时间
        if (isset($data['rStartData']) && $data['rStartData'] && isset($data['rEndData']) && $data['rEndData']) {
            $sqlArr[] = ['receivingTime', '>=', $data['rStartData']];
            $sqlArr[] = ['receivingTime', '<=', $data['rEndData']];
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


    }

    // 写手总览检索
    public function searchWriter(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        if (!$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $shopId = 1;

        $data = [
            'pageNumber' => 1, // 第几页
            'pageSize' => 10, // 一页几条数据
            'writerNum' => '15280392932', // 写手手机号
            'qqAccount' => 'zy239301', // 写手qq
            'wechatAccount' => 'jiosjd', // 写手微信
        ];

        // 页码，条数
        $page = isset($data['pageNumber']) ? intval($data['pageNumber']) : 1;
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

        if (!$request['id'] || !$request['writerInfo']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $shopId = 1;

        $data = [
            'name' => 'dadan', // 写手名
            'qqAccount' => '10929323', // 写手qq
            'wechatAccount' => 'aj293293', // 写手微信
            'alipayAccount' => '102932934@qq.com', // 写手支付宝
        ];

        $num = Writer::where([['id', '=', $request['id'], ['shop_id', '=', $shopId]]])->update([
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

        if (!$request['searchParams']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $shopId = 1;

        $data = [
            'pageNumber' => 1, // 第几页
            'pageSize' => 10, // 一页几条数据
            'writerNum' => '140294402340', // 写手手机号
            'qqAccount' => '109284929@qq.com', // 写手qq号
            'wechatAccount' => 'zy239301', // 写手微信号
            'writerId' => 1, // 写手ID
            'settleState' => 1, // 结算状态(1:已结算，2:未结算, 3:暂缓结算)
            'pStartData' => 0, // 订单付款开始时间
            'pEndData' => 0, // 订单付款结束时间
            'rStartData' => 0, // 订单收货开始时间
            'rEndData' => 0, // 订单收货结束时间
        ];

        // 页码，条数
        $page = isset($data['pageNumber']) ? intval($data['pageNumber']) : 1;
        $limit = isset($data['pageSize']) ? intval($data['pageSize']) : 10;
        $page  = max(1, $page);

        // 跳过的条数
        $offset = $limit * ($page - 1);

        $writerSqlArr = [];
        $writerSqlArr[] = ['shop_id', '<=', $shopId];
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

        // todo 根据写手信息查询订单
        foreach ($writer as $item) {

        }

        // sql语句处理
        $orderSqlArr = [];
        // 默认开始时间
        if (isset($data['pStartData']) && $data['pStartData']) {
            $pStartTime = strtotime($data['pStartData']);
        }
        else {
            $pStartTime = strtotime("2022-01-01");
        }

        // 默认结束时间
        if (isset($data['pEndData']) && $data['pEndData']) {
            $pEndTime = strtotime($data['pEndData']);
        }
        else {
            $pEndTime = time();
        }

        $orderSqlArr[] = ['shop_id', '=', $shopId];
        $orderSqlArr[] = ['paymentTime', '>=', $pStartTime];
        $orderSqlArr[] = ['paymentTime', '<=', $pEndTime];

        // 确认收货时间
        if (isset($data['rStartData']) && $data['rStartData'] && isset($data['rEndData']) && $data['rEndData']) {
            $orderSqlArr[] = ['receivingTime', '>=', $data['rStartData']];
            $orderSqlArr[] = ['receivingTime', '<=', $data['rEndData']];
        }

        // 结算状态
        if (isset($data['settleState']) && $data['settleState']) {
            $orderSqlArr[] = ['settleState', '=', $data['settleState']];
        }

        return oaUsersController::result();
    }

    // 写手报表上传已结算订单
    public function uploadSettled(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
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

    }

    // 写手报表订单全部结算
    public function quickWriterOrderStatus(Request $request)
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

    // 客服报表检索
    public function searchCustomer(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

    }

    // 客服报表批量修改状态
    public function updateAllOrderState(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

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


    }

}
