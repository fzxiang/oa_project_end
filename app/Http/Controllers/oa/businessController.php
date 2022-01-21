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
        $delNum = DB::table('writer_order')->where('orderId', '=', $orderData['id'])->delete();

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

        $failData = [];
        switch ($request['type']) {
            case 1:
                // 上传总览附件
                $failData = $this->overviewData($request['fileData']);
                break;
            case 2:
                // 上传退款附件
                $failData = $this->refundData($request['fileData']);
                break;
        }
    }

    // 上传总览附件
    private function overviewData($datas)
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
            $num = Order::where('aliOrder', '=', $data['aliOrder'])->update([
                'taobaoPrice' => '',
            ]);
        }
    }

    // 上传退款附件
    private function refundData($datas)
    {

    }


    // 导出
    public function exportOrder(Request $request)
    {

    }

    // 我的订单检索
    public function searchOrder(Request $request)
    {

    }

    // 写手总览检索
    public function searchWriter(Request $request)
    {

    }

    // 写手信息编辑
    public function updateWriter(Request $request)
    {

    }

    // 写手报表检索
    public function searchWriterOfKefu(Request $request)
    {

    }

    // 写手报表上传已结算订单
    public function uploadSettled(Request $request)
    {

    }

    // 写手报表订单导出
    public function exportWriter(Request $request)
    {

    }

    // 写手报表订单全部结算
    public function quickWriterOrderStatus(Request $request)
    {

    }

    // 客服报表检索
    public function searchCustomer(Request $request)
    {

    }

    // 客服报表批量修改状态
    public function updateAllOrderState(Request $request)
    {

    }


    // 更新单个单子状态
    public function updateOneOrderState(Request $request)
    {

    }

}
