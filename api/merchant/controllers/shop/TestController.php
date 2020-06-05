<?php

namespace app\controllers\shop;

use app\models\shop\ScoreModel;
use yii;
use yii\web\ShopController;
use yii\db\Exception;


/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TestController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['single', 'test', 'test1'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionSingle() {
//        $amqp = new \tools\amqp\amqp();
//        $conn = $amqp->Connection();
//        $channel = $amqp->Channel($conn);
//        $amqp->Exchange($conn, $channel);
//        $amqp->Queue($channel);
    }

    public function actionTest() {
        if (yii::$app->request->isGet){
            reidsAll();

        }
    }
    

//    public function actionTest1() {
//        $result = json_decode('{"appid":"wxb1d07a2d8ae4c0fb","attach":"shop","bank_type":"ICBC_DEBIT","cash_fee":"100","fee_type":"CNY","is_subscribe":"N","mch_id":"1496441282","nonce_str":"5d034d65a8b78","openid":"o910v5Qyi57vlU9dVbDOEuG9Fejg","out_trade_no":"201906141531487194","result_code":"SUCCESS","return_code":"SUCCESS","sign":"F0103B20A06B41755420154EB717458A","time_end":"20190614153153","total_fee":"100","trade_type":"JSAPI","transaction_id":"4200000309201906149834402416"}', true);
//        //佣金计算 根据比例计算
//        $orderModel = new OrderModel;
//        $orderRs = $orderModel->find(['order_sn' => $result['out_trade_no']]);
//        $orderData = array(
//            'order_sn' => $result['out_trade_no'],
//            'status' => 1,
//        );
//        $orderModel->update($orderData);
//        if ($orderRs['data']['order_type'] == 2) {
//            $shopUserModel = new \app\models\shop\UserModel;
//            $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);
//
//            $tempModel = new \app\models\system\SystemMiniTemplateModel();
//            $minitemp = $tempModel->do_one(['id' => 35]);
//            //单号,金额,下单时间,物品名称,
//            $tempParams = array(
//                'keyword1' => $result['out_trade_no'],
//                'keyword2' => $orderRs['data']['payment_money'],
//                'keyword3' => $orderRs['data']['create_time'],
//                'keyword4' => $orderRs['data']['goodsname'],
//            );
//
//            $tempAccess = new SystemMerchantMiniAccessModel();
//            $taData = array(
//                'key' => $orderRs['data']['key'],
//                'merchant_id' => $orderRs['data']['merchant_id'],
//                'mini_open_id' => $shopUser['data']['mini_open_id'],
//                'template_id' => 35,
//                'number' => '0',
//                'template_params' => json_encode($tempParams),
//                'template_purpose' => 'order',
//                'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$result['out_trade_no']}",
//                'status' => '-1',
//            );
//            $tempAccess->do_add($taData);
//        }
//        //根据订单信息 减去总库存 和 各个商品库存
//        $subOrderModel = new SubOrderModel();
//        $subOrders = $subOrderModel->findall(['order_group_sn' => $result['out_trade_no']]);
//        $number = 0;
//        for ($i = 0; $i < count($subOrders['data']); $i++) {
//            $stockModel = new StockModel();
//            $number = (int) $subOrders['data'][$i]['number'];
//            $stockdata["number = number-{$number}"] = NULL;
//            $stockdata['id'] = $subOrders['data'][$i]['stock_id'];
//            $stockModel->update($stockdata);
//
//
//            $goodModel = new GoodsModel();
//            $gooddata["stocks= stocks-{$subOrders['data'][$i]['number']}"] = null;
//            $gooddata['id'] = $subOrders['data'][$i]['goods_id'];
//            $goodModel->update($gooddata);
//        }
//
//
//
//        $payModel = new PayModel;
//        $paydata = array(
//            'transaction_id' => $result['transaction_id'],
//            'order_id' => $result['out_trade_no'],
//            'remain_price' => $result['total_fee'],
//            'total_price' => $result['total_fee'],
//            'pay_time' => time(),
//            'status' => 1,
//            'merchant_id' => $orderRs['data']['merchant_id'],
//            'user_id' => $orderRs['data']['user_id'],
//            'update_time' => time(),
//        );
//        $res = $payModel->update($paydata);
//        $wxModel = new WeixinModel();
//        $result['wx_appid'] = $result['appid'];
//        $result['wx_mchId'] = $result['mch_id'];
//
//        unset($result['appid']);
//        unset($result['wx_mchId']);
//        $orderModel = new OrderModel;
//        $orderRs = $orderModel->find(['order_sn' => "201906141757118793"]);
//
//        $configModel = new \app\models\tuan\ConfigModel();
//
//        $con = $configModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'key' => $orderRs['data']['key']]);
//
//        if ($con['status'] == 200 && $con['data']['status'] == 1) {
//            $configM = new \app\models\tuan\ConfigModel();
//            $config = $configM->do_one(['merchant_id' => $orderRs['data']['merchant_id']]);
//            $tuanUserModel = new \app\models\tuan\UserModel;
//            $tuanUser = $tuanUserModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'uid' => $orderRs['data']['user_id']]);
//
//            if ($tuanUser['status'] == 204) {
//                $tuanData = array(
//                    'key' => $orderRs['data']['key'],
//                    'merchant_id' => $orderRs['data']['merchant_id'],
//                    'uid' => $orderRs['data']['user_id'],
//                    'leader_uid' => $orderRs['data']['leader_self_uid'],
//                    'status' => 1,
//                );
//                $tuanUserModel->do_add($tuanData);
//                $tuanUser = $tuanUserModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'uid' => $orderRs['data']['user_id']]);
//            }
//
//            $balanceModel = new \app\models\shop\BalanceModel;
//            $balance = $this->balance('201906141750465411', $config['data']['commission_leader_ratio'], $config['data']['commission_leader_ratio']);
//
//            $data = array(
//                'balance_sn' => order_sn(),
//                'uid' => $tuanUser['data']['leader_uid'],
//                'order_sn' => '201906121816518676',
//                'money' => $balance[0],
//                'content' => "团员消费",
//                'type' => 1,
//                'status' => 0
//            );
//            $data['key'] = $orderRs['data']['key'];
//            $data['merchant_id'] = $orderRs['data']['merchant_id'];
//            $array = $balanceModel->do_add($data);
//
//            $balanceModel = new \app\models\shop\BalanceModel;
//
//            $data['money'] = $balance[1];
//            $data['type'] = 3;
//            $data['uid'] = $orderRs['data']['leader_self_uid'];
//            $data['content'] = "自提点佣金";
//            $array = $balanceModel->do_add($data);
//        }
//    }
//
//    public function balance($order_sn, $commission_leader_ratio, $commission_selfleader_ratio) {
//        $money[0] = 0;
//        $money[1] = 0;
//        //根据订单查询子订单
//        $orderSubModel = new \app\models\shop\SubOrderModel();
//        $order = $orderSubModel->findall(['order_group_sn' => $order_sn]);
//        //循环订单 
//        for ($i = 0; $i < count($order['data']); $i++) {
//            // $good = array(); //循环查询商品
//            $goodsModel = new \app\models\shop\GoodsModel();
//            $good = $goodsModel->find(['id' => $order['data'][$i]['goods_id']]);
//            // 判断商品是否单独设置佣金
//            if ($good['data']['commission_leader_ratio'] != 0) {
//                $money[0] = $money[0] + ($order['data'][$i]['payment_money'] * $good['data']['commission_leader_ratio'] / 100);
//            } else {
//                $money[0] = $money[0] + ($order['data'][$i]['payment_money'] * $commission_leader_ratio / 100);
//            }
//            if ($good['data']['commission_selfleader_ratio'] != 0) {
//                $money[1] = $money[1] + ($good['data']['commission_selfleader_ratio'] / 100);
//            } else {
//                $money[1] = $money[1] + ($order['data'][$i]['payment_money'] * $commission_selfleader_ratio / 100);
//            }
//        }
//
//        return $money;
//    }
//
//    public function actionTest() {
////       include __DIR__ . DIRECTORY_SEPARATOR ."saobei". DIRECTORY_SEPARATOR . 'JsPosPrepayRe.php';
////include __DIR__ . DIRECTORY_SEPARATOR ."saobei".  DIRECTORY_SEPARATOR .'JsPrePayDemo.php';
////        if (yii::$app->request->isGet) {
////            $request = yii::$app->request; //获取 request 对象
////            $params = $request->get(); //获取地址栏参数
////            $jsposPrePay = new \JsPosPrepay();
////            $jsposPrePay->setPay_ver("100");
////            $jsposPrePay->setPay_type("010");
////            $jsposPrePay->setService_id("012");
////            $jsposPrePay->setMerchant_no("889100002920008");
////            $jsposPrePay->setTerminal_id("10024667");
////            $jsposPrePay->setTerminal_trace("000229");
////            $jsposPrePay->setTerminal_time(date("YmdHis"));
////            $jsposPrePay->setTotal_fee("1");
////            $jsposPrePay->setOpen_id("o6-BSt2zxyn2910qHAKQkrr0FwL8");
////
////            var_dump(JsPrePayDemo::jsposPrePayRe($jsposPrePay));
////        } else {
////            return result(500, "请求方式错误");
////        }
//    }
//    public function actionTest() {
//        $res = logistics("73111756047544", "ZTO");
//    }
}
