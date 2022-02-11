<?php

namespace App\Http\Controllers\oa;

use App\Exports\CostomerSerRepotExport;
use App\Exports\MyOrderExport;
use App\Exports\TestExport;
use App\Exports\WriterRepotExport;
use App\Http\Controllers\Controller;
use App\Libs\TokenSsl;
use App\Models\HashOrderMaping;
use App\Models\Order;
use App\Models\Role;
use App\Models\Shop;
use App\Models\User;
use App\Models\Writer;
use http\Params;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Excel;

class businessController extends Controller
{
    // 根据hash值获取订单
    private function getOrderIdsOfHash($hash)
    {
        $hashOrder = HashOrderMaping::find($hash);
        if (empty($hashOrder)) {
            return [];
        }

        return json_decode($hashOrder['orderIds'], true);
    }

    // 订单生成hash落库
    private function dataToHashSave($data)
    {
        if (empty($data)) {
            return '';
        }

        sort($data);
        $strData = json_encode($data);
        $hash = TokenSsl::generateHash($strData);

        // 库里存在该hash不能重复插入
        $hashOrder = HashOrderMaping::find($hash);
        if ($hashOrder) {
            return $hash;
        }

        // 不存在该hash数据落库
        $curTime = time();
        $hashData = HashOrderMaping::create([
            'strHash' => $hash,
            'orderIds' => $strData,
            'operate' => $curTime,
        ]);
        return $hash;
    }

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
            'taobaoPrice'       => $orderData['taobaoPrice'] ?? 0,
            'serWritePrice'     => $orderData['taobaoPrice'] ?? 0,
            'customerContact'   => $orderData['customerContact'] ?? '',
            'orderOutline'      => $orderData['orderOutline'] ?? '',
            'remarks'           => $orderInfo['other']['remarks'] ?? '',
        ]);

        // 添加写手数据
        $writerData = $orderInfo['writer'];
        if (!empty($writerData)) {
            $writerOrderArr = [];
            foreach ($writerData as $item) {
                $writerSituation = $item['writerSituation'] ?? 0;
                $writerNum = $item['writerNum'] ?? 0;
                // 写手表无需重复添加同一个写手
                $writerSql = Writer::where([['writerNum', '=', $writerNum], ['shop_id', '=', $shopId]])->get()->toArray();
                !empty($writerSql) && $writerSql = $writerSql[0];
                if (empty($writerSql)) {
                    $writerSql = Writer::create([
                        'shop_id'           => $shopId,
                        'writerNum'         => $writerNum,
                        'name'              => $item['name'] ?? '',
                        'alipayAccount'     => $item['alipayAccount'] ?? '',
                        'qqAccount'         => $item['qqAccount'] ?? '',
                        'wechatAccount'     => $item['wechatAccount'] ?? '',
                        'writerSituation'   => intval($writerSituation),
                        'writerQuality'     => $item['writerQuality'] ?? '',
                    ]);
                }

                $writerOrderArr[] = [
                    'shop_id'           => $shopId,
                    'writerId' => $writerSql['id'],
                    'orderId' => $order['id'],
                    'writerPrice' => $item['writerPrice'] ?? 0,
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
            'taobaoPrice'       => $orderData['taobaoPrice'] ?? 0,
            'serWritePrice'     => $orderData['taobaoPrice'] ?? 0,
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
                $writerSituation = $item['writerSituation'] ?? 0;
                if (!$item['id']) {
                    $writer = Writer::create([
                        'writerNum'         => $item['writerNum'] ?: 0,
                        'name'              => $item['name'] ?? '',
                        'alipayAccount'     => $item['alipayAccount'] ?? '',
                        'qqAccount'         => $item['qqAccount'] ?? '',
                        'wechatAccount'     => $item['wechatAccount'] ?? '',
                        'writerSituation'   => intval($writerSituation),
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
                        'writerSituation'   => intval($writerSituation),
                        'writerQuality'     => $item['writerQuality'] ?? '',
                    ]);

                    $writerOrderArr[] = [
                        'shop_id'           => $shopId,
                        'writerId' => $item['id'],
                        'orderId' => $orderData['id'],
                        'writerPrice' => $item['writerPrice'] ?? 0,
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
            return oaUsersController::result([],-1, '订单编号重复');
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
//                'actualPaymentPrice' => 100, // 买家实际支付金额
//                'confirmTime' => strtotime("2022-01-01 12:00:00"), // 确认收货时间
//                'paymentTime' => strtotime("2022-01-02 14:00:00"), // 确认付款时间
//            ],
//            [
//                'aliOrder' => '192103299124', // 订单编号
//                'paymentMer' => 50, // 打款商家金额
//                'actualPaymentPrice' => 50, // 买家实际支付金额
//                'confirmTime' => strtotime("2022-01-03 08:00:00"), // 确认收货时间
//                'paymentTime' => strtotime("2022-01-04 15:00:00"), // 确认付款时间
//            ],
//            [
//                'aliOrder' => '14700238239489', // 订单编号
//                'paymentMer' => 0, // 打款商家金额
//                'actualPaymentPrice' => 0, // 买家实际支付金额
//                'confirmTime' => strtotime("2022-01-05 08:00:00"), // 确认收货时间
//                'paymentTime' => strtotime("2022-01-06 08:00:00"), // 确认付款时间
//            ],
//        ];

        $failData = [];
        $oldTime = time();

        $sqlUpdates = [];

        DB::beginTransaction();
        // 上传附件数据处理
        foreach ($datas as $data) {
//            $order = Order::where([['aliOrder', '=', $data['aliOrder']], ['shop_id', '=', $shopId]])->get()->toArray();
//            if (!$order) {
//                $failData[] = $data['aliOrder'];
//                continue;
//            }

            $updates = [];
            isset($data['paymentMer']) && $updates['taobaoPrice'] = $data['paymentMer'];
            isset($data['paymentTime']) && $updates['paymentTime'] = $data['paymentTime'];
            isset($data['confirmTime']) && $updates['receivingTime'] = $data['confirmTime'];
            isset($data['paymentMer']) && $updates['overviewFilePrice'] = $data['paymentMer'];
            isset($data['actualPaymentPrice']) && $updates['actualPaymentPrice'] = $data['actualPaymentPrice'];

            $sqlUpdates[] = $updates;
            // 更新数据
            $num = Order::where([['aliOrder', '=', $data['aliOrder']], ['shop_id', '=', $shopId]])->update($updates);

//            DB::table('order')->where('aliOrder', '=', $data['aliOrder'])
//                ->where('shop_id', '=', $shopId)->update($updates);

            if (!$num) {
                $failData[] = $data['aliOrder'];
            }
        }

        DB::commit();

        $newTime = time();
        $aaa = $newTime - $oldTime;
        $str = '开始时间：'.$oldTime.', 结束时间：'.$newTime.', 时间差：'.$aaa.'秒';

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
                'refundPrice' => $data['refundMoney'],
            ]);
        }
        return $failData;
    }


    // 我的订单导出
    public function exportOrder(Request $request)
    {
//        $token = $request->header('Authorization');

        $param = json_decode($request['obj'], true);

        $token = $param['token'];
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $shopId = intval($param['shop']);
//        $shopId = $request['shop'];
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        $request['searchParams'] = $param['searchParams'];

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
//            $pStartTime = strtotime("2022-01-01");
            $pStartTime = 0;
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
//            $pEndTime = time();
            $pEndTime = 0;
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

        $data = [];
        // 导出字断
        $fileField = [
            '店铺名称', '客服ID', '接单客服', '淘宝订单号', '会员名', '打款商家金额', '买家退款金额', '买家实际支付金额',
            '总表(打款商家金额)-退款表(买家退款金额)', "客服填写价格", "最终对比价格", "订单概要", "订单付款时间", "确认收货时间",
            '写手名 01', '写手QQ 01', '写手手机号 01', '写手派单价 01', '写手支付宝 01', '写手情况 01', '写手质量 01',
            '写手名 02', '写手QQ 02', '写手手机号 02', '写手派单价 02', '写手支付宝 02', '写手情况 02', '写手质量 02',
            '写手名 03', '写手QQ 03', '写手手机号 03', '写手派单价 03', '写手支付宝 03', '写手情况 03', '写手质量 03',
        ];

        $curTime = time();

        $data[] = $fileField;

        $shop = Shop::find($shopId)->toArray();

        foreach ($order as $item) {
            $user = User::find($item['acceptUser'])->toArray();

            $itemData = [
                $shop['shop_name'],
                $item['acceptUser'],
                $user['username'],
                $item['aliOrder'],
                $item['memberName'],
                $item['overviewFilePrice'],
                $item['refundPrice'],
                $item['actualPaymentPrice'],
                $item['taobaoPrice'],
                $item['serWritePrice'],
                $item['taobaoPrice'],
                $item['orderOutline'],
                date('Y-m-d H:i:s', $item['paymentTime']),
                date('Y-m-d H:i:s', $item['receivingTime']),
            ];

            // 写手信息查询
            $writerOrders = DB::table('writer_order')->where('orderId', '=', $item['id'])
                ->where('shop_id', '=', $shop['shop_id'])
                ->get()->toArray();

            foreach ($writerOrders as $writerOrder) {
                $writerId = $writerOrder->writerId;

                $writer = Writer::find($writerId)->toArray();
                $itemData[] = $writer['name'];
                $itemData[] = $writer['qqAccount'];
                $itemData[] = $writer['writerNum'];
                $itemData[] = $writerOrder->writerPrice;
                $itemData[] = $writer['alipayAccount'];
                $itemData[] = $writer['writerSituation'];
                $itemData[] = $writer['writerQuality'];
            }

            $data[] = $itemData;
        }

        return Excel::download(new MyOrderExport($data), '我的订单导出.xlsx');
    }

    // 我的订单检索
    public function searchOrder(Request $request)
    {
        $token = $request->header('Authorization');
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

        $user_id = $data['user_id'];
        $shopId = $request->header('Shop');
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        $request['searchParams'] = json_decode($request['searchParams'], true);

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
//            $pStartTime = strtotime("2022-01-01");
            $pStartTime = 0;
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
//            $pEndTime = time();
            $pEndTime = 0;
        }

        $sqlArr[] = ['shop_id', '=', $shopId];
        $pStartTime && $sqlArr[] = ['paymentTime', '>=', $pStartTime];
        $pEndTime && $sqlArr[] = ['paymentTime', '<=', $pEndTime];

        // 接单客服
        $sqlArr[] = ['acceptUser', '=', $user_id];

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

        foreach ($order as $k => $item) {
            $writerOrder = DB::table('writer_order')->where('orderId', '=', $item['id'])
                ->where('shop_id', '=', $shopId)->get()->toArray();

            $user = User::find($item['acceptUser']);

            $orderWriterPrice = 0;
            foreach ($writerOrder as $obj) {
                $orderWriterPrice += $obj->writerPrice;
            }

            $order[$k]['writerTotalPrice'] = $orderWriterPrice;
            $order[$k]['acceptUser'] = $user['username'];
        }

        // 所有订单
        $totalOrders = Order::where($sqlArr)->get()->toArray();
        $count = Order::where($sqlArr)->count();
        // 计算总价格
        $taobaoTotalPrice = 0;  // 淘宝总价格
        $writerTotalPrice = 0;  // 写手总价格
        foreach ($totalOrders as $item) {
            $taobaoTotalPrice += $item['taobaoPrice'];
            $writerOrder = DB::table('writer_order')->where('orderId', '=', $item['id'])
                ->where('shop_id', '=', $shopId)->get()->toArray();

            foreach ($writerOrder as $obj) {
                $writerTotalPrice += $obj->writerPrice;
            }
        }

        $relt = [
            'items' => $order,
            'tbTotalPrice' => $taobaoTotalPrice,
            'writerTotalPrice' => $writerTotalPrice,
            'total' => $count,
        ];
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

        $request['searchParams'] = json_decode($request['searchParams'], true);
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
        $count  = Writer::where($sqlArr)->count();

        $relt = [
            'items' => $writer,
            'total' => $count,
        ];

        return oaUsersController::result($relt);
    }

    // 写手总览检索
    public function getAllWriter(Request $request)
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

        $writer = Writer::where('shop_id', '=', $shopId)->get()->toArray();

        return oaUsersController::result($writer);
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

        $sqlUpdates = [];
        isset($data['name']) && $sqlUpdates['name'] = $data['name'];
        isset($data['qqAccount']) && $sqlUpdates['qqAccount'] = $data['qqAccount'];
        isset($data['wechatAccount']) && $sqlUpdates['wechatAccount'] = $data['wechatAccount'];
        isset($data['alipayAccount']) && $sqlUpdates['alipayAccount'] = $data['alipayAccount'];

        $num = Writer::where([['id', '=', $request['id']], ['shop_id', '=', $shopId]])->update($sqlUpdates);

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

        $request['searchParams'] = json_decode($request['searchParams'], true);
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

        $jsonInfo = $this->writerReportSearch($shopId, $data);
        $priceInfo = $this->writerReportSearchTotalPrice($shopId, $data);

        $relt = [
            'items' => $jsonInfo,
            'totalPrice' => $priceInfo['totalPrice'],
            'settlePrice' => $priceInfo['settlePrice'],
            'noSettlePrice' => $priceInfo['noSettlePrice'],
            'total' => $priceInfo['count'],
        ];

        return oaUsersController::result($relt);
    }

    private function writerReportSearch($shopId, $data)
    {
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
            $writerSqlArr[] = ['id', '=', $data['writerId']];
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
                'writerSituation' => intval($item['writerSituation']),
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
//            $pStartTime = strtotime("2022-01-01");
            $pStartTime = 0;
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
//            $pEndTime = time();
            $pEndTime = 0;
        }

        // 确认收货时间
        $rStartTime = $rEndTime = 0;
        if (isset($data['rStartTime']) && isset($data['rEndTime'])) {
            $rStartTime = strtotime($data['rStartTime']);
            $rEndTime = strtotime($data['rEndTime']);
        }

        $settleState = $data['settleState'] ?? 0;

        $writerOrderInfos = [];
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

            $user = User::find($order['acceptUser']);
            $jsonInfo[$item->writerId]['childOrder'][] = [
                'id' => $order['id'],
                'invoice' => $order['invoice'],
                'acceptUser' => $user['username'],
                'aliOrder' => $order['aliOrder'],
                'wSettleState' => $item->wSettleState,
                'taobaoPrice' => $order['taobaoPrice'],
                'writerPrice' => $item->writerPrice,
                'paymentTime' => $order['paymentTime'],
                'receivingTime' => $order['receivingTime'],
            ];

            $curOrderNum = $writerOrderInfos[$item->writerId]['count'] ?? 0;
            $curWriterPrice = $writerOrderInfos[$item->writerId]['price'] ?? 0;
            $writerOrderInfos[$item->writerId] = [
                'count' => $curOrderNum + 1,
                'price' => $curWriterPrice + $item->writerPrice,
            ];
        }

        // 写手对应订单处理
        foreach ($writerOrderInfos as $key => $item) {
            // 订单数
            $jsonInfo[$key]['orderNum'] = $item['count'];
            // 总金额
            $jsonInfo[$key]['totalWriterPrice'] = $item['price'];
        }

        return array_values($jsonInfo);
    }

    private function writerReportSearchTotalPrice($shopId, $data)
    {
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
            $writerSqlArr[] = ['id', '=', $data['writerId']];
        }

        $writer = Writer::where($writerSqlArr)->get()->toArray();

        $count = Writer::where($writerSqlArr)->count();

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
                'writerSituation' => intval($item['writerSituation']),
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
//            $pStartTime = strtotime("2022-01-01");
            $pStartTime = 0;
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
//            $pEndTime = time();
            $pEndTime = 0;
        }

        // 确认收货时间
        $rStartTime = $rEndTime = 0;
        if (isset($data['rStartTime']) && isset($data['rEndTime'])) {
            $rStartTime = strtotime($data['rStartTime']);
            $rEndTime = strtotime($data['rEndTime']);
        }

        $settleState = $data['settleState'] ?? 0;

        $writerOrderInfos = [];
        // 符合条件数据拼接
        $writerTotalPrice = 0;
        $isSettlePrice = 0;
        $noSettlePrice = 0;

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

            $writerTotalPrice += $item->writerPrice;
            if ($item->wSettleState == 1) {
                $isSettlePrice += $item->writerPrice;
            }else{
                $noSettlePrice += $item->writerPrice;
            }

        }

        $infos = [
            'totalPrice' => $writerTotalPrice,
            'settlePrice' => $isSettlePrice,
            'noSettlePrice' => $noSettlePrice,
            'count' => $count,
        ];
        return $infos;
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

        $datas = [
            [
                'alipayAccount' => '234453fe', // 收款方支付宝账号
                'name' => '张静', // 收款方姓名
                'price' => 43, // 金额
                'invoice' => '6a9a03c1', // 单号
            ],
            [
                'alipayAccount' => '234453fe2', // 收款方支付宝账号
                'name' => '张静', // 收款方姓名
                'price' => 25, // 金额
                'invoice' => '91f86f0e', // 单号
            ],
            [
                'alipayAccount' => '234453fe1', // 收款方支付宝账号
                'name' => '张静', // 收款方姓名
                'price' => 546, // 金额
                'invoice' => '6a9a03c1', // 单号
            ],
        ];

        $datas = $request['fileData'];

        $failData = [];
        foreach ($datas as $data) {
            if (!$data['invoice']) {
                $failData[] = $data['alipayAccount'];
                continue;
            }

            // 订单号解析
            $orderIds = $this->getOrderIdsOfHash($data['invoice']);
            if (empty($orderIds)) {
                $failData[] = $data['alipayAccount'];
                continue;
            }

            // 写手信息获取
            $writer = Writer::where([['shop_id', '=', $shopId], ['alipayAccount', '=', $data['alipayAccount']]])->get()->toArray();
            if (empty($writer)) {
                $failData[] = $data['alipayAccount'];
                continue;
            }
            $writer = $writer[0];
            $writerId = $writer['id'];

            // 订单处理
            foreach ($orderIds as $orderId) {
                // 写手结算状态
                $writerNum = DB::table('writer_order')->where('writerId', '=', $writerId)
                    ->where('orderId', '=', $orderId)
                    ->update([
                        'wSettleState' => 1,
                    ]);

                // 订单结算状态
                $order = Order::find($orderId);
                $order['settleState'] = 1;
                $order->save();
            }
        }

        return oaUsersController::result($failData);
    }

    // 写手报表订单导出
    public function exportWriter(Request $request)
    {
//        $token = $request->header('Authorization');

        $param = json_decode($request['obj'], true);

        $token = $param['token'];
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

//        $shopId = $request->header('Shop');
        $shopId = $param['shop'];
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        $request['searchParams'] = $param['searchParams'];
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

        $jsonInfo = $this->writerReportSearch($shopId, $data);

        $fileDatas = [];
        $fileField = [
            '序号', '收款方支付宝账号', '收款方姓名', '金额', '备注',
        ];

        $fileDatas[] = ['支付宝批量付款文件模板（前两行请勿删除）', '','','','','', '填写说明：'];

        $fileDatas[] = $fileField;

        foreach ($jsonInfo as $key => $item) {
            // 获取该写手检索后查询出来的所有订单ID
            $writerIds = [];
            foreach ($item['childOrder'] as $child) {
                $writerIds[] = $child['id'];
            }

            // 所有ID转成hash存储
            $hashSql = $this->dataToHashSave($writerIds);

            $fileDatas[] = [
                $item['id'],
                $item['alipayAccount'],
                $item['name'],
                $item['totalWriterPrice'],
                $hashSql,
            ];
        }

        for ($i = 1; $i < 16; ++$i) {
            if (empty($fileDatas[$i])) {
                $fileDatas[$i] = ['', '', '', '', ''];
            }
        }

        $fileDatas[1][] = '';
        $fileDatas[1][] = '注意事项';
        $fileDatas[1][] = '1.请勿删除或增加列';
        $fileDatas[2][] = '';
        $fileDatas[2][] = '';
        $fileDatas[2][] = '2.请勿删除表头，即文件头两行';
        $fileDatas[3][] = '';
        $fileDatas[3][] = '';
        $fileDatas[3][] = '3.一个文件可以包含3000笔明细，超过3000笔可分多个文件上传；每次可以上传一个文件';
        $fileDatas[4][] = '';
        $fileDatas[4][] = '';
        $fileDatas[4][] = '4.上传文件目前支持的Excel版本为1997-2003版本，csv文件无要求';
        $fileDatas[5][] = '';
        $fileDatas[5][] = '';
        $fileDatas[5][] = '5.系统不支持同名文件上传，会提示重复上传，修改文件名后重新上传即可';
        $fileDatas[6][] = '';
        $fileDatas[6][] = '';
        $fileDatas[6][] = '6.金额一列如果出现"明细金额异常"报错，请先复制金额到txt文本中，从text文本复制到Excel，另外还可通过Excel，text函数进行格式转换';
        $fileDatas[7] = ['','',''];
        $fileDatas[8] = ['','',''];
        $fileDatas[9][] = '';
        $fileDatas[9][] = '填写说明';
        $fileDatas[9][] = '字断名称';
        $fileDatas[9][] = '是否可选项';
        $fileDatas[9][] = '填写说明（1个汉字占2个字节，1个标点符号/数字/英文字母占1个字节）';
        $fileDatas[10][] = '';
        $fileDatas[10][] = '';
        $fileDatas[10][] = '文件名命名';
        $fileDatas[10][] = '必输项';
        $fileDatas[10][] = '最大长度100个字节，支持中文、英文、数字、下划线（_）、中划线（-）。';
        $fileDatas[11][] = '';
        $fileDatas[11][] = '';
        $fileDatas[11][] = '序号';
        $fileDatas[11][] = '必输项';
        $fileDatas[11][] = '最大长度64个字节，不可重复，必须为数字。';
        $fileDatas[12][] = '';
        $fileDatas[12][] = '';
        $fileDatas[12][] = '收款方支付宝账号';
        $fileDatas[12][] = '必输项';
        $fileDatas[12][] = '最大长度100个字节，收款人实名认证的支付宝登录邮箱或手机账户登录名，支持个人/企业支付宝支付宝账户收款。';
        $fileDatas[13][] = '';
        $fileDatas[13][] = '';
        $fileDatas[13][] = '收款方实名认证名称';
        $fileDatas[13][] = '必输项';
        $fileDatas[13][] = '最大长度100个字节，收款人支付宝账户实名认证的名称。个人账户为姓名，企业账户为企业名称';
        $fileDatas[14][] = '';
        $fileDatas[14][] = '';
        $fileDatas[14][] = '付款金额';
        $fileDatas[14][] = '必输项';
        $fileDatas[14][] = '最大长度13位，须为正确表示金额的数字，精确到两位小数';
        $fileDatas[15][] = '';
        $fileDatas[15][] = '';
        $fileDatas[15][] = '备注';
        $fileDatas[15][] = '选输项';
        $fileDatas[15][] = '最大长度100个字节，可输入附言或工号';

        return Excel::download(new WriterRepotExport($fileDatas), '写手报表导出.xlsx');
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

        if (!$request['writeId'] || !$request['searchParams']) {
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
//            $pStartTime = strtotime("2022-01-01");
            $pStartTime = 0;
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
//            $pEndTime = time();
            $pEndTime = 0;
        }

        // 确认收货时间
        $rStartTime = $rEndTime = 0;
        if (isset($data['rStartTime']) && isset($data['rEndTime'])) {
            $rStartTime = strtotime($data['rStartTime']);
            $rEndTime = strtotime($data['rEndTime']);
        }

        // 查询当前写手所有订单ID
        $writerOrders = DB::table('writer_order')->where('writerId', '=', $request['writeId'])
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

    // 写手报表单个订单结算
    public function updateWriteOrderState(Request $request)
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

        if (!$request['writeId'] || !$request['orderId'] || !$request['state']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $num = DB::table('writer_order')->where('writerId', '=', $request['writeId'])
            ->where('orderId', '=', $request['orderId'])->update([
                'wSettleState' => $request['state']
            ]);

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

        $request['searchParams'] = json_decode($request['searchParams'], true);

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

        $order = $this->kefuCheckSearch($shopId, $data);

        $priceInfo = $this->kefuCheckSearchPrice($shopId, $data);

        $relt = [
            'items' => $order,
            'tbTotalPrice' => $priceInfo['tbTotalPrice'],
            'writerTotalPrice' => $priceInfo['writerTotalPrice'],
            'total' => $priceInfo['count'],
        ];

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
//            $pStartTime = strtotime("2022-01-01");
            $pStartTime = 0;
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
//            $pEndTime = time();
            $pEndTime = 0;
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

        foreach ($order as $k => $item) {
            $writerOrder = DB::table('writer_order')->where('orderId', '=', $item['id'])
                ->where('shop_id', '=', $shopId)->get()->toArray();

            $orderWriterPrice = 0;
            foreach ($writerOrder as $obj) {
                $orderWriterPrice += $obj->writerPrice;
            }

            $user = User::find($item['acceptUser']);

            $order[$k]['acceptUser'] = $user['username'];
            $order[$k]['writerTotalPrice'] = $orderWriterPrice;
        }

        return $order;
    }

    private function kefuCheckSearchPrice($shopId, $params)
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
//            $pStartTime = strtotime("2022-01-01");
            $pStartTime = 0;
        }

        // 默认结束时间
        if (isset($data['pEndTime']) && $data['pEndTime']) {
            $pEndTime = strtotime($data['pEndTime']);
        }
        else {
//            $pEndTime = time();
            $pEndTime = 0;
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

        // 所有订单
        $totalOrders = Order::where($sqlArr)->get()->toArray();

        $count = Order::where($sqlArr)->count();
        // 计算总价格
        $taobaoTotalPrice = 0;  // 淘宝总价格
        $writerTotalPrice = 0;  // 写手总价格
        foreach ($totalOrders as $item) {
            $taobaoTotalPrice += $item['taobaoPrice'];
            $writerOrder = DB::table('writer_order')->where('orderId', '=', $item['id'])
                ->where('shop_id', '=', $shopId)->get()->toArray();

            foreach ($writerOrder as $obj) {
                $writerTotalPrice += $obj->writerPrice;
            }
        }

        $infos = [
            'tbTotalPrice' => $taobaoTotalPrice,
            'writerTotalPrice' => $writerTotalPrice,
            'count' => $count,
        ];
        return $infos;
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

        if (!$request['state'] || !$request['searchParams']) {
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
//        $token = $request->header('Authorization');
        $param = json_decode($request['obj'], true);

        $token = $param['token'];
        // 用户未登陆
        if (!$data = oaUsersController::getUserIdOfToken($token)) {
            return oaUsersController::result([],-1, 'err_token');
        }

//        $shopId = $request->header('Shop');
        $shopId = $param['shop'];
        if (!$shopId) {
            return oaUsersController::result([],-1, 'err_shop');
        }

        $request['searchParams'] = $param['searchParams'];

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

        $fileField = [
            '发单号', '接单客服', '淘宝订单编号', '会员名', '淘宝价格', '写手派单价格', '结算状态', '订单付款时间', '确认收货时间',
        ];

        $fileDatas = [];
        $fileDatas[] = ['客服报表'];
        $fileDatas[] = $fileField;

        foreach ($relt as $order) {
            $user = User::find($order['acceptUser'])->toArray();

            $writerOrders = DB::table('writer_order')->where('orderId', '=', $order['id'])
                ->where('shop_id', '=', $shopId)
                ->get()->toArray();
            // 派单总价格
            $writerPrice = 0;
            foreach ($writerOrders as $item) {
                $writerPrice += $item->writerPrice ?? 0;
            }

            $fileDatas[] = [
                $order['invoice'],
                $user['username'],
                $order['aliOrder'],
                $order['memberName'],
                $order['taobaoPrice'],
                $writerPrice,
                $order['settleState'],
                date('Y-m-d H:i:s', $order['paymentTime']),
                date('Y-m-d H:i:s', $order['receivingTime']),
            ];
        }

        return Excel::download(new CostomerSerRepotExport($fileDatas), '客服报表导出.xlsx');
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

        if (!$request['id']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $orderWriters = DB::table('writer_order')->where('orderId', '=', $request['id'])
            ->where('shop_id', '=', $shopId)
            ->get()->toArray();

        $writerInfo = [];
        foreach ($orderWriters as $item) {
            $writer = Writer::find($item->writerId);
            if (empty($writer)) {
                continue;
            }

            $writerInfo[] = [
                'id' => $writer['id'],
                'writerNum' => $writer['writerNum'],
                'name' => $writer['name'],
                'alipayAccount' => $writer['alipayAccount'],
                'qqAccount' => $writer['qqAccount'],
                'wechatAccount' => $writer['wechatAccount'],
                'writerSituation' => intval($writer['writerSituation']),
                'writerQuality' => intval($writer['writerQuality']),
                'compensateState' => $item->compensateState,
                'wSettleState' => $item->wSettleState,
                'writerPrice' => $item->writerPrice,
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

        if (!$request['id']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $orderWriters = DB::table('writer_order')->where('writerId', '=', $request['id'])
            ->where('shop_id', '=', $shopId)
            ->get()->toArray();

        $orderInfo = [];
        foreach ($orderWriters as $item) {
            $order = Order::find($item->orderId);
            if (empty($order)) {
                continue;
            }

            $userId = $order['acceptUser'] ?? 0;
            $user = User::find($userId);

            $orderInfo[] = [
                'id' => $order['id'],
                'invoice' => $order['invoice'],
                'acceptUser' => $user['username'],
                'aliOrder' => $order['aliOrder'],
                'settleState' => $order['settleState'],
                'taobaoPrice' => $order['taobaoPrice'],
                'paymentTime' => $order['paymentTime'],
                'receivingTime' => $order['receivingTime'],
                'writerPrice' => $item->writerPrice,
            ];
        }

        return oaUsersController::result($orderInfo);
    }

    // 写手关联订单查询
    public function getWriterInfo(Request $request)
    {
        if (!$request['writerNum']) {
            return oaUsersController::result([],-1, 'err_param');
        }

        $writer = Writer::where('writerNum', '=', $request['writerNum'])->get()->toArray();

        $info = [];
        foreach ($writer as $item) {
            $writerOrders = DB::table('writer_order')->where('writerId', '=', $item['id'])
                ->get()->toArray();

            foreach ($writerOrders as $writerOrder) {
                $info[] = [
                    'writerId' => $writerOrder->writerId,
                    'orderId' => $writerOrder->orderId,
                    'shop_id' => $writerOrder->shop_id,
                    'writerPrice' => $writerOrder->writerPrice,
                    'wSettleState' => $writerOrder->wSettleState,
                    'compensateState' => $writerOrder->compensateState,
                ];
            }
        }

        return oaUsersController::result($info);
    }

}
