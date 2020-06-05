<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/18 9:03
 */

namespace app\controllers\pay;

use app\models\admin\system\SystemSmsModel;
use app\models\merchant\distribution\AgentModel;
use app\models\merchant\distribution\OperatorModel;
use app\models\merchant\distribution\SuperModel;
use app\models\shop\ShopAssembleAccessModel;
use app\models\system\SystemSmsTemplateAccessModel;
use app\models\system\SystemSmsTemplateIdModel;
use Qcloud\Sms\SmsSingleSender;
use yii;
use WxPay\Wechat;
use yii\web\Controller;
use app\models\core\WxConfigModel;
use app\models\merchant\app\AppAccessModel;
use app\models\shop\MerchantCategoryModel;
use app\models\merchant\pay\PayModel;
use app\models\merchant\app\ComboModel;
use app\models\core\TableModel;
use app\models\pay\WeixinModel;
use app\models\shop\OrderModel;
use app\models\system\SystemWxConfigModel;
use app\models\merchant\app\AppModel;
use app\models\merchant\forum\ForumModel;
use app\models\forum\UserModel;
use app\models\shop\SubOrderModel;
use app\models\shop\StockModel;
use app\models\shop\GoodsModel;
use EasyWeChat\Factory;
use app\models\wolive\BusinessModel;
use app\models\wolive\ServiceModel;
use app\models\merchant\user\MerchantModel;
use app\models\merchant\system\GroupModel;
use app\models\system\SystemMerchantMiniAccessModel;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');

class WechatController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\TokenFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['index', 'qrcode', 'notify', 'notify1','query', 'notifyreturn', 'notify-sao-bei'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    private $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA'
    ];

    /**
     * 扫码支付，获取二维码
     * @return array
     */
    public function actionIndex($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->post(); //获取地址栏参数
        $wx = new Wechat();
        //获取商户微信配置
        $params['id'] = $id;
        if (!isset($params['id'])) {
            return result(500, "缺少请求参数 订单号");
        }

        $payModel = new PayModel();
        $payinfo = $payModel->find($params);
        if ($payinfo['status'] != 200) {
            return result(500, "无效订单号");
        }
        if ($payinfo['data']['status'] != 2) {
            return result(500, "无效订单");
        }
        $appAccessModel = new AppAccessModel();
        $appAccess = $appAccessModel->find(['id' => $payinfo['data']['app_access_id']]);
        if ($appAccess['status'] != 200) {
            return result(500, "套餐无效");
        }
        $comboModel = new ComboModel();
        $combo = $comboModel->find(['id' => $appAccess['data']['combo_id']]);
        if ($combo['status'] != 200) {
            return result(500, "套餐已下架");
        }

        try {
            $appModel = new AppModel();
            $app = $appModel->find(['id' => $appAccess['data']['app_id']]);
            if ($app['status'] == 200) {
                if ($app['data']['category_id'] == 1) {
                    $data['trade_no'] = "forum_" . $params['id'];
                    $data['attach'] = "app";
                } else if ($app['data']['category_id'] == 2) {
                    $data['trade_no'] = "shop_" . $params['id'];
                    $data['attach'] = "app";
                }
            } else {
                return result(500, "找不到APP信息");
            }

            $data['name'] = $combo['data']['name'];
            $data['money'] = $payinfo['data']['remain_price'];
            $data['goos_tag'] = $combo['data']['name'];
            $data['notify_url'] = "http://api.juanpao.com/pay/wechat/notify";

            $config = json_decode(json_encode(yii::$app->params['wx_config']), false);
            $result = $wx->wxPayUnifiedOrder($data, $config);
        } catch (\Exception $e) {
            $array = [
                'status' => 500,
                'message' => '二维码获取失败',
            ];
            return $array;
        }
        $array = [
            'status' => 200,
            'message' => '请求成功',
            // 'data' => 'http://192.168.188.12/pay/wechat/qrcode?data=' . $result,
            'out_trade_no' => $params['id'],
            'data' => 'http://api.juanpao.com/pay/wechat/qrcode?data=' . $result,
        ];
        //  }


        return $array;
    }

    /**
     * 订单查询
     * @throws \Exception
     */
    public function actionQuery($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数
        //判断必填
        $payModel = new PayModel();
        $payinfo = $payModel->find(['id' => $id]);
        $appAccessModel = new AppAccessModel();
        $appAccess = $appAccessModel->find(['id' => $payinfo['data']['app_access_id']]);
        $appModel = new AppModel();
        $app = $appModel->find(['id' => $appAccess['data']['app_id']]);
        if ($app['status'] == 200) {
            if ($app['data']['category_id'] == 1) {
                $params['out_trade_no'] = "forum_" . $params['id'];
            } else if ($app['data']['category_id'] == 2) {
                $params['out_trade_no'] = "shop_" . $params['id'];
            }
        } else {
            return result(500, "找不到APP信息");
        }
//        $must = ['out_trade_no'];
//        $checkRes = $this->checkInput($must, $params);
//        if ($checkRes != false) {
//            return json_encode($checkRes);
//        }
        //获取商户微信配置

        $config = $config = json_decode(json_encode(yii::$app->params['wx_config']), false);
        //执行微信请求
        $wx = new Wechat();
        try {
            $result = $wx->orderQuery($params['out_trade_no'], $config);
        } catch (\Exception $e) {
            $array = [
                'status' => 500,
                'message' => '内部错误',
            ];
            return $array;
        }
        if ($result['return_code'] == 'FAIL') {
            $array = [
                'status' => 500,
                'message' => '请求参数错误',
                'return_msg' => $result['return_msg'],
            ];
            return $array;
        }
        if ($result['result_code'] == 'FAIL') {
            $array = [
                'status' => 500,
                'message' => '查询失败',
                'err_code' => $result['err_code'],
                'err_code_des' => $result['err_code_des'],
            ];
            return $array;
        }
        if ($result['trade_state'] == "SUCCESS") {
            $arr = [
                'out_trade_no' => $result['out_trade_no'], //订单编号
                'total_fee' => $result['total_fee'], //标价金额
                'trade_state' => $result['trade_state'], //交易状态
                'trade_state_desc' => $result['trade_state_desc'], //交易状态描述
                'trade_type' => $result['trade_type'], //交易类型
            ];
        } else {
            $arr = [
                'out_trade_no' => $result['out_trade_no'], //订单编号
                'trade_state' => $result['trade_state'], //交易状态
            ];
        }
        $array = [
            'status' => 200,
            'message' => '请求成功',
            'data' => $arr,
        ];
        return $array;
    }

    /**
     * 退款
     * @return array
     */
    public function actionRefund() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->post(); //获取传递参数
        //判断必填
        $must = ['out_trade_no', 'merchant_id', 'total_fee', 'refund_fee'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return json_encode($checkRes);
        }
        //获取商户微信配置
        $wxConfig = new WxConfigModel();
        $configRes = $wxConfig->getConfig($params['merchant_id']);
        if ($configRes['status'] != 200) {
            return $configRes;
        }
        $config = $configRes['data'];
        $wx = new Wechat();
        try {
            $result = $wx->orderRefund($params, $config);
        } catch (\Exception $e) {
            $array = [
                'status' => 500,
                'message' => '退款请求失败',
            ];
            return $array;
        }
        if ($result['return_code'] == 'FAIL') {
            $array = [
                'status' => 500,
                'message' => '请求参数错误',
                'return_msg' => $result['return_msg'],
            ];
            return $array;
        }
        if ($result['result_code'] == 'FAIL') {
            $array = [
                'status' => 500,
                'message' => '退款失败',
                'err_code' => $result['err_code'],
                'err_code_des' => $result['err_code_des'],
            ];
            return $array;
        }
        $arr = [
            'out_trade_no' => $result['out_trade_no'], //订单编号
            'out_refund_no' => $result['out_refund_no'], //商户退款单号
            'refund_fee' => $result['refund_fee'], //退款金额
            'total_fee' => $result['total_fee'], //标价金额
        ];
        $array = [
            'status' => 200,
            'message' => '请求成功',
            'data' => $arr,
        ];
        return $array;
    }

    /**
     * 退款查询
     * @return array
     */
    public function actionRefundquery() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数
        //判断必填
        $must = ['out_trade_no', 'merchant_id'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return json_encode($checkRes, JSON_UNESCAPED_UNICODE);
        }
        //获取商户微信配置
        $wxConfig = new WxConfigModel();
        $configRes = $wxConfig->getConfig($params['merchant_id']);
        if ($configRes['status'] != 200) {
            return json_encode($configRes, JSON_UNESCAPED_UNICODE);
        }
        $config = $configRes['data'];
        $wx = new Wechat();
        try {
            $result = $wx->refundQuery($params['out_trade_no'], $config);
        } catch (\Exception $e) {
            $array = [
                'status' => 500,
                'message' => '内部错误',
            ];
            return $array;
        }
        if ($result['return_code'] == 'FAIL') {
            $array = [
                'status' => 500,
                'message' => '请求参数错误',
                'return_msg' => $result['return_msg'],
            ];
            return $array;
        }
        if ($result['result_code'] == 'FAIL') {
            $array = [
                'status' => 500,
                'message' => '查询失败',
                'err_code' => $result['err_code'],
                'err_code_des' => $result['err_code_des'],
            ];
            return $array;
        }
        $arr = [
            'out_trade_no' => $result['out_trade_no'], //订单编号
            'refund_count' => $result['refund_count'], //退款笔数
            'refund_fee' => $result['refund_fee'], //退款金额
            'total_fee' => $result['total_fee'], //标价金额
        ];
        $array = [
            'status' => 200,
            'message' => '请求成功',
            'data' => $arr,
        ];
        return $array;
    }

    /**
     * 关闭订单
     * @return array
     */
    public function actionClose() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->post(); //获取地址栏参数
        //判断必填
        $must = ['out_trade_no', 'merchant_id'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return json_encode($checkRes, JSON_UNESCAPED_UNICODE);
        }
        //获取商户微信配置
        $wxConfig = new WxConfigModel();
        $configRes = $wxConfig->getConfig($params['merchant_id']);
        if ($configRes['status'] != 200) {
            return json_encode($configRes, JSON_UNESCAPED_UNICODE);
        }
        $config = $configRes['data'];
        $wx = new Wechat();
        try {
            $result = $wx->refundClose($params['out_trade_no'], $config);
        } catch (\Exception $e) {
            $array = [
                'status' => 500,
                'message' => '内部错误',
            ];
            return json_encode($array, JSON_UNESCAPED_UNICODE);
        }
        if ($result['return_code'] == 'FAIL') {
            $array = [
                'status' => 500,
                'message' => '请求参数错误',
                'return_msg' => $result['return_msg'],
            ];
            return $array;
        }
        if ($result['result_code'] == 'FAIL') {
            $array = [
                'status' => 500,
                'message' => '关闭失败',
                'err_code' => $result['err_code'],
                'err_code_des' => $result['err_code_des'],
            ];
            return $array;
        }
        $array = [
            'status' => 200,
            'message' => '请求成功',
        ];
        return $array;
    }

    /**
     * 支付回调 线上环境
     * @throws \EasyWeChat\Kernel\Exceptions\HttpException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \WxPayException
     * @throws yii\db\Exception
     */
    public function actionNotify() {
        //获取商户微信配置
        $xml = file_get_contents("php://input");
        file_put_contents(Yii::getAlias('@webroot/') . '/test.text', date('Y-m-d H:i:s') . 'xxx' . PHP_EOL, FILE_APPEND);

        $wxPatNotify = new \WxPayNotify();
        $wxPatNotify->Handle(false);
        $returnValues = $wxPatNotify->GetValues();
        $result = $wxPatNotify->FromXml($xml);
        $this->logger(json_encode($result));
        file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
//        $a = '{"appid":"wxe8bceb47d563824d","attach":"shop","bank_type":"CFT","cash_fee":"2","fee_type":"CNY","is_subscribe":"N","mch_id":"1496441282","nonce_str":"5d259694aaea1","openid":"oQiQX0V7OwdylihmiFRkwYqS8fJE","out_trade_no":"201907101541081467","result_code":"SUCCESS","return_code":"SUCCESS","sign":"805B07FAE298BF3A4E7B632CC775DF2D","time_end":"20190710154113","total_fee":"2","trade_type":"JSAPI","transaction_id":"4200000332201907103792432943"}';
//        $result = json_decode($a, true);
        if (!empty($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            //商户逻辑处理，如订单状态更新为已支付
            $out_trade_no = $result['out_trade_no'];
            $out_trade_no = explode("_", $out_trade_no);
            //圈子
            if ($result['attach'] == "app") {
                if ($out_trade_no[0] == "forum") {
                    $trade_no = $result['transaction_id'];
                    $payModel = new PayModel();
                    $params['id'] = $out_trade_no[1];
                    $params['type'] = 2;
                    $params['status'] = 1;
                    $params['pay_time'] = time();
                    $params['transaction_id'] = $trade_no;
                    $payModel->update($params);

                    $payinfo = $payModel->find(['id' => $out_trade_no[1]]);
                    $appAccessModel = new AppAccessModel();
                    $appAccess = $appAccessModel->find(['id' => $payinfo['data']['app_access_id']]);
                    $comboModel = new ComboModel();
                    $comboinfo = $comboModel->find(['id' => $appAccess['data']['combo_id']]);
                    $data['expire_time'] = strtotime(date('Y-m-d', strtotime("+{$comboinfo['data']['expired_days']}day")));
                    $data['id'] = $payinfo['data']['app_access_id'];
                    $data['status'] = 1;
                    $rs = $appAccessModel->update($data);

                    unset($data['expire_time']);
                    unset($data['status']);
                    $apppAccess = $appAccessModel->find($data);
                    $forumModel = new ForumModel();
                    $config = array(
                        'must_keyword' => 0,
                        'must_examine' => 0,
                        'allow_post_time' => 0,
                        'allow_comment_level' => 0,
                        'illegally' => "",
                        'score' => false,
                    );

                    $array = array(
                        '`key`' => $apppAccess['data']['key'],
                        'name' => $apppAccess['data']['name'],
                        'merchant_id' => $apppAccess['data']['merchant_id'],
                        'pic_url' => $apppAccess['data']['pic_url'],
                        'detail_info' => $apppAccess['data']['detail_info'],
                        'config' => json_encode($config),
                        'status' => 1,
                    );
                    $forumModel->add($array);

                    $foromUserModel = new UserModel();
                    $userdata = array(
                        '`key`' => $apppAccess['data']['key'],
                        'avatar' => $apppAccess['data']['pic_url'],
                        'merchant_id' => $apppAccess['data']['merchant_id'],
                        'nickname' => '管理员',
                        'sex' => '1',
                        'is_admin' => 9,
                        'status' => 1
                    );
                    $foromUserModel->add($userdata);


                    $systemConfigModel = new SystemWxConfigModel();
                    $systemConfigdata['merchant_id'] = $appAccess['data']['merchant_id'];
                    $systemConfigdata['`key`'] = $appAccess['data']['key'];
                    $systemConfigdata['wechat'] = json_encode(array(
                        "type" => 0,
                        "wechat_id" => 0,
                        "app_id" => "",
                        "url" => "https://api.juanpao.com/wx?key={$appAccess['data']['key']}",
                        "secret" => "",
                        "token" => generateCode(32),
                        "aes_key" => generateCode(43)
                    ));
                    $systemConfigdata['wechat_pay'] = json_encode(array(
                        "type" => 0,
                        "app_id" => "",
                        "mch_id" => "",
                        "cert_path" => "",
                        "key_path" => "",
                        'notify_url' => "http://api.juanpao.com/pay/wechat/notify",
                    ));
                    $systemConfigdata['miniprogram'] = "";
                    $systemConfigModel->add($systemConfigdata);
                }
                //商城
                if ($out_trade_no[0] == "shop") {
                    $trade_no = $result['transaction_id'];
                    $payModel = new PayModel();
                    $params['id'] = $out_trade_no[1];
                    $params['type'] = 2;
                    $params['status'] = 1;
                    $params['pay_time'] = time();
                    $params['transaction_id'] = $trade_no;
                    $payModel->update($params);

                    $payinfo = $payModel->find(['id' => $out_trade_no[1]]);

                    $appAccessModel = new AppAccessModel();
                    $appAccess = $appAccessModel->find(['id' => $payinfo['data']['app_access_id']]);
                    $comboModel = new ComboModel();
                    $comboinfo = $comboModel->find(['id' => $appAccess['data']['combo_id']]);
                    $data['expire_time'] = strtotime(date('Y-m-d', strtotime("+{$comboinfo['data']['expired_days']}day")));
                    $data['id'] = $payinfo['data']['app_access_id'];
                    $data['status'] = 1;
                    $data['config'] = '{"is_large_scale":"1","number":"100000"}';
                    $rs = $appAccessModel->update($data);

                    $systemConfigModel = new SystemWxConfigModel();
                    $systemConfigdata['merchant_id'] = $appAccess['data']['merchant_id'];
                    $systemConfigdata['`key`'] = $appAccess['data']['key'];
                    $systemConfigdata['wechat'] = json_encode(array(
                        "type" => 0,
                        "wechat_id" => 0,
                        "app_id" => "",
                        "url" => "https://api.juanpao.com/wx?key={$appAccess['data']['key']}",
                        "secret" => "",
                        "token" => generateCode(32),
                        "aes_key" => generateCode(43)
                    ));
                    $systemConfigdata['wechat_pay'] = json_encode(array(
                        "type" => 0,
                        "app_id" => "",
                        "mch_id" => "",
                        "cert_path" => "",
                        "key_path" => "",
                        'notify_url' => "http://api.juanpao.com/pay/wechat/notify",
                    ));
                    $systemConfigdata['miniprogram'] = "";
                    $systemConfigModel->add($systemConfigdata);

                    $merchantModel = new MerchantModel();
                    $merchant = $merchantModel->find(['id' => $appAccess['data']['merchant_id']]);

                    $businessModel = new BusinessModel();
                    $bdata = array(
                        'business_id' => $appAccess['data']['key'],
                        'video_state' => 'close',
                        'voice_state' => 'open',
                        'audio_state' => 'open',
                        'distribution_rule' => 'auto',
                        'voice_address' => '/upload/voice/default.mp3',
                        'state' => 'open'
                    );
                    $businessModel->add($bdata);
                    $serviceModel = new ServiceModel();
                    $sdata = array(
                        'user_name' => $appAccess['data']['key'],
                        'nick_name' => $appAccess['data']['name'],
                        'real_name' => $merchant['data']['real_name'],
                        'password' => $merchant['data']['password'],
                        'salt' => $merchant['data']['salt'],
                        'groupid' => '0',
                        'phone' => $merchant['data']['phone'],
                        'email' => '',
                        'business_id' => $appAccess['data']['key'],
                        'avatar' => $merchant['data']['pic_url'],
                        'level' => 'super_manager',
                        'parent_id' => '0',
                        'state' => 'offline',
                    );
                    $serviceModel->add($sdata);

                    $groupModel = new GroupModel();
                    $rdata = array(
                        'key' => $appAccess['data']['key'],
                        'merchant_id' => $appAccess['data']['merchant_id'],
                        'title' => '客服',
                        'status' => 1,
                        'create_time' => time(),
                        'is_kefu' => 1,
                    );
                    $groupModel->add($rdata);

                    $merchatComboModel = new \app\models\merchant\system\MerchantComboModel();
                    $combo = $merchatComboModel->do_one(['type' => 9]);

                    $merchatComboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
                    $order = "combo_" . date("YmdHis", time()) . rand(1000, 9999);
                    $comboData = array(
                        'merchant_id' => $appAccess['data']['merchant_id'],
                        'key' => $appAccess['data']['key'],
                        'order_sn' => $order,
                        'combo_id' => $params['id'],
                        'sms_number' => $combo['data']['sms_number'],
                        'order_number' => $combo['data']['order_number'],
                        'sms_remain_number' => $combo['data']['sms_number'],
                        'order_remain_number' => $combo['data']['order_number'],
                        'validity_time' => $combo['data']['validity_time'],
                        'type' => $combo['data']['type'],
                        'remarks' => "购买应用赠送",
                        'status' => 0,
                    );

                    $res = $merchatComboAccessModel->do_add($comboData);
                }
            }
            if ($result['attach'] == "shop") {
                //检测订单是否是拼团订单 是的话订单状态11
                $groupAccModel = new ShopAssembleAccessModel();
                $groupWhere['order_sn'] =  $result['out_trade_no'];
                $groupInfo = $groupAccModel->one($groupWhere);
                $orderModel = new OrderModel;
                $orderRs = $orderModel->find(['order_sn' => $result['out_trade_no']]);


                $status = 1;
                if($groupInfo['status'] == 200){
                    $status = 11;
                }else{
                    if($orderRs['data']['service_goods_status'] == 1){
                        $status = 3;
                    }
                }
                $orderData = array(
                    'order_sn' => $result['out_trade_no'],
                    'status' => $status,
                );
                $orderModel->update($orderData);

                //将订单号放入redis队列，用计划任务计算分销分佣金额
                $dtbData['order_sn'] = $result['out_trade_no'];
                lpushRedis('distribution',$dtbData);

                //微信公众号商家，新订单提醒
                $messageData['key'] = $orderRs['data']['key'];
                $messageData['pay_time'] = time();
                lpushRedis('wechat_template_message',$messageData);

                //易联云自动推送，将订单号、key放入redis队列
                $ylyData['key'] = $orderRs['data']['key'];
                $ylyData['supplier_id'] = $orderRs['data']['supplier_id'];
                $ylyData['order_sn'] = $result['out_trade_no'];
                if ($orderRs['data']['supplier_id'] == 0){
                    //非门店订单
                    lpushRedis('ylyprint',$ylyData);
                    file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "待打印_". json_encode($ylyData) . PHP_EOL, FILE_APPEND);
                }else{
                    //门店订单
                    lpushRedis('supplier_ylyprint',$ylyData);
                    file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "待打印_". json_encode($ylyData) . PHP_EOL, FILE_APPEND);
                }

                //好物圈导入订单
                //查询好物圈插件是否开启
                $appAccessModel = new AppAccessModel();
                $appAccessInfo = $appAccessModel->find(['`key`' => $orderRs['data']['key']]);

                if (isset($appAccessInfo['status']) && $appAccessInfo['status'] == 200 && $appAccessInfo['data']['good_phenosphere'] == 1){
                    $categoryModel = new MerchantCategoryModel();
                    $sql = "SELECT so.*,sg.m_category_id FROM `shop_order_group` sog LEFT JOIN `shop_order` so ON so.order_group_sn = sog.order_sn LEFT JOIN `shop_goods` sg ON sg.id = so.goods_id WHERE sog.`key` = '".$orderRs['data']['key']."' AND sog.order_sn = '".$result['out_trade_no']."'";
                    $goodsData = $orderModel->querySql($sql);
                    //商品分类
                    $sql = "SELECT * FROM `shop_marchant_category` WHERE `key` = '".$orderRs['data']['key']."' AND delete_time is NULL";
                    $categoryData = $categoryModel->querySql($sql);
                    if (count($goodsData)>0 || count($categoryData)>0) {
                        $config = $this->getSystemConfig($orderRs['data']['key'], "miniprogram");
                        if ($config == false) {
                            file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "未配置小程序信息" . PHP_EOL, FILE_APPEND);
                        } else {
                            $miniProgram = Factory::miniProgram($config);
                            $token = $miniProgram->access_token->getToken(true);// 强制重新从微信服务器获取 token
                            if (!isset($token['access_token'])){
                                file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "小程序access_token不存在" . PHP_EOL, FILE_APPEND);
                            } else {
                                $miniProgram['access_token']->setToken($token['access_token'], 3600);
                                $access_token = $token['access_token'];
                                $url = "https://api.weixin.qq.com/mall/importorder?action=add-order&is_history=0&access_token={$access_token}";
                                $circleData[0]['order_id'] = $result['out_trade_no'];  //订单id，需要保证唯一性
                                $circleData[0]['create_time'] = $orderRs['data']['create_time'];  //订单创建时间，unix时间戳
                                $circleData[0]['pay_finish_time'] = time();  //支付完成时间，unix时间戳
                                $circleData[0]['trans_id'] = $result['transaction_id'];  //微信支付订单id，对于使用微信支付的订单，该字段必填
                                $circleData[0]['fee'] = $result['cash_fee'];  //订单金额，单位：分
                                $circleData[0]['status'] = 3;//订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                                $circleData[0]['ext_info'] = [
                                    'express_info'=>[
                                        'price'=>$orderRs['data']['express_price']*100 //运费，单位：分
                                    ],  //快递信息
                                    'brand_info'=>[
                                        'contact_detail_page'=>[
                                            'kf_type'=>1   //在线客服类型 1 没有在线客服; 2 微信客服消息; 3 小程序自有客服; 4 公众号h5自有客服
                                        ]  //联系商家页面
                                    ],  //商家信息
                                    'payment_method'=>1,  //订单支付方式，0：未知方式 1：微信支付 2：其他支付方式
                                    'user_open_id'=>$result['openid'],  //用户的openid，参见openid说明
                                    'order_detail_page'=>[
                                        'path'=>'pages/orderItem/orderItem/orderItem?order_sn='.$result['out_trade_no']  //小程序订单详情页跳转链接
                                    ],  //订单详情页（小程序页面）
                                ];  //订单扩展信息
                                foreach ($goodsData as $gk=>$gv){
                                    if ($gv['order_group_sn'] == $result['out_trade_no']){
                                        $categoryList = [];
                                        foreach ($categoryData as $ck=>$cv){
                                            if ($gv['m_category_id'] == $cv['id']){
                                                $categoryList[] = $cv['name'];
                                                foreach ($categoryData as $pk => $pv){
                                                    if ($pv['id'] == $cv['parent_id']){
                                                        $categoryList[] = $pv['name'];
                                                    }
                                                }
                                            }
                                        }
                                        $categoryList = array_reverse($categoryList);
                                        $circleData[0]['ext_info']['product_info']['item_list'][] = [
                                            'item_code'=>$gv['goods_id'],  //物品ID（SPU ID），要求appid下全局唯一
                                            'sku_id'=>$gv['goods_id'],  //sku_id
                                            'amount'=>$gv['number'],  //物品数量
                                            'total_fee'=>$gv['total_price']*100,  //物品总价，单位：分
                                            'thumb_url'=>$gv['pic_url'],  //物品图片，图片宽度必须大于750px，宽高比建议4:3 - 1:1之间
                                            'title'=>$gv['name'],  //物品名称
                                            'unit_price'=>$gv['price']*100,  //物品单价（实际售价），单位：分
                                            'original_price'=>$gv['price']*100,  //物品原价，单位：分
                                            'category_list'=>$categoryList,  //物品类目列表
                                            'item_detail_page'=>['path'=>'pages/goodsItem/goodsItem/goodsItem?id='.$gv['goods_id']],  //小程序物品详情页跳转链接
                                            'can_be_search'=>true
                                        ];  //物品相关信息
                                    }
                                }
                                $orderList['order_list'] = $circleData;
                                $array = json_encode($orderList, JSON_UNESCAPED_UNICODE);
                                $rs = curlPost($url, $array);
                                file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . $rs . PHP_EOL, FILE_APPEND);
                            }
                        }
                    } else {
                        file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "未查询到订单商品信息" . PHP_EOL, FILE_APPEND);
                    }
                }

                //商家发货短信提醒
                $smsModel = new SystemSmsModel();
                $smsWhere['type'] = 1; //腾讯云
                $smsWhere['status'] = 1;
                $smsInfo = $smsModel->do_one($smsWhere); //查询腾讯云配置
                $templateIdModel = new SystemSmsTemplateIdModel();
                $templateIdInfo = $templateIdModel->do_one([]); //查询商家发货提醒短信模板id
                if ($orderRs['data']['supplier_id'] == 0){ //查询商家电话
                    $appModel = new AppAccessModel();
                    $appInfo = $appModel->find(['key'=>$orderRs['data']['key']]);
                    if ($appInfo['status'] == 200 && !empty($appInfo['data']['phone'])){
                        $merchantPhone = $appInfo['data']['phone'];
                    }
                }else{
                    $subUserModel = new \app\models\merchant\system\UserModel();
                    $subUserInfo = $subUserModel->find(['id'=>$orderRs['data']['supplier_id']]);
                    if ($subUserInfo['status'] == 200){
                        $supplierInfo = json_decode($subUserInfo['data']['leader'],true);
                        $merchantPhone = $supplierInfo['phone'];
                    }
                }
                if ($smsInfo['status'] == 200 && $templateIdInfo['status'] == 200 && isset($merchantPhone)){
                    $templateConfig = json_decode($templateIdInfo['data']['config'],true);
                    if ($templateConfig[0]['status'] == 'true'){
                        $smsAccessModel = new SystemSmsTemplateAccessModel();
                        $smsAccessWhere['phone'] = $merchantPhone;
                        $smsAccessWhere['type'] = 1; //商家发货提醒
                        $smsAccessInfo = $smsAccessModel->do_one($smsAccessWhere);
                        //离上次给商家发短信超过1小时才能再发
                        if ($smsAccessInfo['status'] == 204 || (isset($smsAccessInfo['data']) && ($smsAccessInfo['data']['create_time'] + 3600) < time())){
                            $smsInfo['data']['config'] = json_decode($smsInfo['data']['config'],true);
                            try {
                                $sender = new SmsSingleSender($smsInfo['data']['config']['appid'], $smsInfo['data']['config']['appkey']);
                                $sendResult = $sender->sendWithParam("86",$merchantPhone,$templateConfig[0]['templateId']);
                                $sendRes = json_decode($sendResult, true);
                            } catch (\Exception $e) {

                            }
                            if (isset($sendRes['result']) && $sendRes['result'] == 0){
                                $smsAccessData['phone'] = $merchantPhone;
                                $smsAccessData['template_id'] = $templateConfig[0]['templateId'];
                                $smsAccessData['type'] = 1; //商家发货提醒
                                $smsAccessModel->do_add($smsAccessData);
                            }else{
                                $sms_error['result'] = $sendRes['result'];
                                $sms_error['errmsg'] = unicodeDecode($sendRes['errmsg']);
                                file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . json_encode($sms_error, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
                            }
                        }else{
                            file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '一小时内只发给商家一次' . PHP_EOL, FILE_APPEND);
                        }
                    }else{
                        file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '商家发货短信提醒未开启' . PHP_EOL, FILE_APPEND);
                    }
                }else{
                    file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '未查询到商家电话或腾讯云、短信模板配置信息' . PHP_EOL, FILE_APPEND);
                }

                if ($orderRs['data']['order_type'] == 2) {
                    $shopUserModel = new \app\models\shop\UserModel;
                    $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);

                    $tempModel = new \app\models\system\SystemMiniTemplateModel();
                    $minitemp = $tempModel->do_one(['id' => 35]);
                    //单号,金额,下单时间,物品名称,
                    $tempParams = array(
                        'keyword1' => $result['out_trade_no'],
                        'keyword2' => $orderRs['data']['payment_money'],
                        'keyword3' => $orderRs['data']['create_time'],
                        'keyword4' => $orderRs['data']['goodsname'],
                    );

                    $tempAccess = new SystemMerchantMiniAccessModel();
                    $taData = array(
                        'key' => $orderRs['data']['key'],
                        'merchant_id' => $orderRs['data']['merchant_id'],
                        'mini_open_id' => $shopUser['data']['mini_open_id'],
                        'template_id' => 35,
                        'number' => '0',
                        'template_params' => json_encode($tempParams),
                        'template_purpose' => 'order',
                        'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$result['out_trade_no']}",
                        'status' => '-1',
                    );
                    $tempAccess->do_add($taData);
                }
                //根据订单信息 减去总库存 和 各个商品库存
                $subOrderModel = new SubOrderModel();
                $subOrders = $subOrderModel->findall(['order_group_sn' => $result['out_trade_no']]);
                $number = 0;

                for ($i = 0; $i < count($subOrders['data']); $i++) {
                    if ($subOrders['data'][$i]['is_flash_sale'] == 0) {
                        $stockModel = new StockModel();
                        $number = (int) $subOrders['data'][$i]['number'];
                        $stockdata["number = number-{$number}"] = NULL;
                        $stockdata['id'] = $subOrders['data'][$i]['stock_id'];
                        $stockModel->update($stockdata);
                        $goodModel = new GoodsModel();
                        $gooddata["stocks= stocks-{$subOrders['data'][$i]['number']}"] = null;
                        $gooddata['id'] = $subOrders['data'][$i]['goods_id'];
                        $goodModel->update($gooddata);
                    } else {
                        $flashModel = new \app\models\spike\FlashSaleModel();
                        $flashGoods = $flashModel->do_one(['goods_id' => $subOrders['data'][$i]['goods_id']]);
                        $property = explode("-", $flashGoods['property']);
                        $str = "";
                        for ($j = 0; $i < count($property); $j++) {
                            $a = json_decode($property[$j], true);
                            if ($a['stock_id'] == $subOrders['data'][$i]['stock_id']) {
                                $a['stock'] = $a['stock'] - $number;
                            }
                            if ($j == 0) {
                                $str = json_encode($a, JSON_UNESCAPED_UNICODE);
                            } else {
                                $str = $str . "_" . json_encode($a, JSON_UNESCAPED_UNICODE);
                            }
                        }
                        $flashModel->do_update(['goods_id' => $subOrders['data'][$i]['goods_id']], ['property' => $str]);
                    }

                    $goodsModel = new \app\models\shop\SaleGoodsModel();
                    $goods = $goodsModel->do_one(['id' => $subOrders['data'][$i]['goods_id']]);
                    if ($goods['data']['supplier_id'] != 0) {
                        $systemSubUserBalanceModel = new \app\models\system\SystemSubAdminBalanceModel();
                        $data = array(
                            'key' => $goods['data']['key'],
                            'merchant_id' => $goods['data']['merchant_id'],
                            'sub_admin_id' => $goods['data']['supplier_id'],
                            'order_sn' => $result['out_trade_no'],
                            'money' => $goods['data']['supplier_money'],
                            'content' => "供应商商品出售佣金",
                            'status' => 1,
                            'type' => 6,
                        );
                        $systemSubUserBalanceModel->do_add($data);
                    }
                }


                $payModel = new PayModel;
                $paydata = array(
                    'transaction_id' => $result['transaction_id'],
                    'order_id' => $result['out_trade_no'],
                    'remain_price' => $result['total_fee'],
                    'total_price' => $result['total_fee'],
                    'pay_time' => time(),
                    'status' => 1,
                    'merchant_id' => $orderRs['data']['merchant_id'],
                    'user_id' => $orderRs['data']['user_id'],
                    'update_time' => time(),
                );
                $res = $payModel->update($paydata);
                $wxModel = new WeixinModel();
                $result['wx_appid'] = $result['appid'];
                $result['wx_mchId'] = $result['mch_id'];

                unset($result['appid']);
                unset($result['wx_mchId']);

                //佣金计算 根据比例计算
                $configModel = new \app\models\tuan\ConfigModel();

                $con = $configModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'key' => $orderRs['data']['key']]);
                if ($con['status'] == 200 && $con['data']['status'] == 1) {
                    $tuanUserModel = new \app\models\tuan\UserModel;
                    $tuanUser = $tuanUserModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'uid' => $orderRs['data']['user_id']]);

                    if ($tuanUser['status'] == 204) {
                        $tuanData = array(
                            'key' => $orderRs['data']['key'],
                            'merchant_id' => $orderRs['data']['merchant_id'],
                            'uid' => $orderRs['data']['user_id'],
                            'leader_uid' => $orderRs['data']['leader_self_uid'],
                            'status' => 1,
                        );
                        $tuanUserModel->do_add($tuanData);
                        $tuanUser = $tuanUserModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'uid' => $orderRs['data']['user_id']]);
                    }

                    if ($orderRs['data']['express_type'] == 2) {
                        $leaderModel = new \app\models\tuan\LeaderModel();
                        $leader = $leaderModel->do_one(['uid' => $orderRs['data']['leader_self_uid']]);

                        $balanceModel = new \app\models\shop\BalanceModel;
                        $data['money'] = $leader['data']['tuan_express_fee'];
                        $data['type'] = 6;
                        $data['uid'] = $orderRs['data']['leader_self_uid'];
                        $data['content'] = "配送费佣金";
                        $array = $balanceModel->do_add($data);
                    }

                    $balanceModel = new \app\models\shop\BalanceModel;
                    $balance = $this->balance($result['out_trade_no'], $con['data']['commission_leader_ratio'], $con['data']['commission_selfleader_ratio']);
                    $data = array(
                        'uid' => $tuanUser['data']['leader_uid'],
                        'order_sn' => $result['out_trade_no'],
                        'money' => $balance[0],
                        'content' => "团员消费",
                        'type' => 1,
                        'status' => 0
                    );
                    $data['key'] = $orderRs['data']['key'];
                    $data['merchant_id'] = $orderRs['data']['merchant_id'];
                    $array = $balanceModel->do_add($data);
                    $balanceModel = new \app\models\shop\BalanceModel;
                    if ($orderRs['data']['leader_self_uid'] != 0) {
                        $data['money'] = $balance[1];
                        $data['type'] = 3;
                        $data['uid'] = $orderRs['data']['leader_self_uid'];
                        $data['content'] = "自提点佣金";
                        $array = $balanceModel->do_add($data);
                    }
                }
            }
            $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
            $comboAccessData = $comboAccessModel->do_one(['<>' => ['order_remain_number', 0], '>' => ['validity_time', time()], 'merchant_id' => $orderRs['data']['merchant_id']]);

            $comboAccessModel->do_update(['id' => $comboAccessData['data']['id']], ['order_remain_number' => $comboAccessData['data']['order_remain_number'] - 1]);
            $systemPayWeixin = new WeixinModel();
            $result['status'] = 1;
            $result['create_time'] = time();

            $rs = $systemPayWeixin->add($result);

            if ($rs['status'] == 200) {
                ob_clean();
                echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                die();
            } else {
                ob_clean();
                echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
                die();
            }
        }
    }

    public function actionNotify1()
    {
        //获取商户微信配置
        $xml = file_get_contents("php://input");
        //     file_put_contents(Yii::getAlias('@webroot/') . '/test.text', date('Y-m-d H:i:s') . 'xxx' . PHP_EOL, FILE_APPEND);

        $wxPatNotify = new \WxPayNotify();
        $wxPatNotify->Handle(false);
        $returnValues = $wxPatNotify->GetValues();
        $result = $wxPatNotify->FromXml($xml);
        // $this->logger(json_encode($result));
        //file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
//         $a = '{"appid":"wx5efa471126f441c3","attach":"shop","bank_type":"COMM_DEBIT","cash_fee":"2190","fee_type":"CNY","is_subscribe":"N","mch_id":"1537270121","nonce_str":"5e7f0d2aeb6c5","openid":"onH725QknPbegejzrVKNVoQioGHk","out_trade_no":"t_202003281639062352","result_code":"SUCCESS","return_code":"SUCCESS","sign":"30C936E58CEB1C396BF321E6840EFFA8","time_end":"20200328163912","total_fee":"2190","trade_type":"JSAPI","transaction_id":"4200000559202003288521530202"}';
//        $result = json_decode($a, true);
        if (!empty($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            //商户逻辑处理，如订单状态更新为已支付
            $out_trade_no = $result['out_trade_no'];
            //  $out_trade_no = explode("_", $out_trade_no);

            if ($result['attach'] == "shop") {
                //检测订单是否是拼团订单 是的话订单状态11
                $groupAccModel = new ShopAssembleAccessModel();
                $groupWhere['order_sn'] = $result['out_trade_no'];
                $groupInfo = $groupAccModel->one($groupWhere);
                $orderModel = new OrderModel;
                $orderRes = $orderModel->findList(['transaction_order_sn' => $result['out_trade_no']]);
                $number = 1;
                if ($orderRes['status'] == 200) {
                    $number = count($orderRes['data']);
                }
                for ($l = 0; $l < $number; $l++) {
                    $status = 1;
                    if ($groupInfo['status'] == 200) {
                        $status = 11;
                    } else {
                        if ($orderRes['data'][$l]['service_goods_status'] == 1) {
                            $status = 3;
                        }
                    }
                    $orderData = array(
                        'transaction_order_sn' => $result['out_trade_no'],
                        'status' => $status,
                    );

                    $orderRs['status'] = 200;
                    $orderRs['data'] = $orderRes['data'][$l];
                    $result['out_trade_no'] = $orderRes['data'][$l]['order_sn'];

                    $appAccessModel = new AppAccessModel();
                    $appInfo = $appAccessModel->find(['key'=>$orderRs['data']['key']]);

                    $orderData['commission'] = $this->fenxiao($orderRes['data'][$l]['order_sn'], $appInfo['data']['distribution']);
                    $orderModel->update($orderData);

                    //  $orderRs['data'] = array();

                    //将订单号放入redis队列，用计划任务计算分销分佣金额
                    $dtbData['order_sn'] = $result['out_trade_no'];
                    lpushRedis('distribution',$dtbData);

                    //微信公众号商家，新订单提醒
                    $messageData['key'] = $orderRs['data']['key'];
                    $messageData['pay_time'] = time();
                    lpushRedis('wechat_template_message',$messageData);

                    //易联云自动推送，将订单号、key放入redis队列
                    $ylyData['key'] = $orderRs['data']['key'];
                    $ylyData['supplier_id'] = $orderRs['data']['supplier_id'];
                    $ylyData['order_sn'] = $result['out_trade_no'];
                    if ($orderRs['data']['supplier_id'] == 0){
                        //非门店订单
                        lpushRedis('ylyprint',$ylyData);
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "待打印_". json_encode($ylyData) . PHP_EOL, FILE_APPEND);
                    }else{
                        //门店订单
                        lpushRedis("supplier_ylyprint",$ylyData);
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "待打印_". json_encode($ylyData) . PHP_EOL, FILE_APPEND);
                    }

                    //好物圈导入订单
                    // 查询好物圈插件是否开启
                    $appAccessModel = new AppAccessModel();
                    $appAccessInfo = $appAccessModel->find(['`key`' => $orderRs['data']['key']]);

                    if (isset($appAccessInfo['status']) && $appAccessInfo['status'] == 200 && $appAccessInfo['data']['good_phenosphere'] == 1){
                        $categoryModel = new MerchantCategoryModel();
                        $sql = "SELECT so.*,sg.m_category_id FROM `shop_order_group` sog LEFT JOIN `shop_order` so ON so.order_group_sn = sog.order_sn LEFT JOIN `shop_goods` sg ON sg.id = so.goods_id WHERE sog.`key` = '".$orderRs['data']['key']."' AND sog.order_sn = '".$result['out_trade_no']."'";
                        $goodsData = $orderModel->querySql($sql);
                        //商品分类
                        $sql = "SELECT * FROM `shop_marchant_category` WHERE `key` = '".$orderRs['data']['key']."' AND delete_time is NULL";
                        $categoryData = $categoryModel->querySql($sql);
                        if (count($goodsData)>0 || count($categoryData)>0) {
                            $config = $this->getSystemConfig($orderRs['data']['key'], "miniprogram");
                            if ($config == false) {
                                file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "未配置小程序信息" . PHP_EOL, FILE_APPEND);
                            } else {
                                $miniProgram = Factory::miniProgram($config);
                                $token = $miniProgram->access_token->getToken(true);// 强制重新从微信服务器获取 token
                                if (!isset($token['access_token'])){
                                    file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "小程序access_token不存在" . PHP_EOL, FILE_APPEND);
                                } else {
                                    $miniProgram['access_token']->setToken($token['access_token'], 3600);
                                    $access_token = $token['access_token'];
                                    $url = "https://api.weixin.qq.com/mall/importorder?action=add-order&is_history=0&access_token={$access_token}";
                                    $circleData[0]['order_id'] = $result['out_trade_no'];  //订单id，需要保证唯一性
                                    $circleData[0]['create_time'] = $orderRs['data']['create_time'];  //订单创建时间，unix时间戳
                                    $circleData[0]['pay_finish_time'] = time();  //支付完成时间，unix时间戳
                                    $circleData[0]['trans_id'] = $result['transaction_id'];  //微信支付订单id，对于使用微信支付的订单，该字段必填
                                    $circleData[0]['fee'] = $result['cash_fee'];  //订单金额，单位：分
                                    $circleData[0]['status'] = 3;//订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                                    $circleData[0]['ext_info'] = [
                                        'express_info'=>[
                                            'price'=>$orderRs['data']['express_price']*100 //运费，单位：分
                                        ],  //快递信息
                                        'brand_info'=>[
                                            'contact_detail_page'=>[
                                                'kf_type'=>1   //在线客服类型 1 没有在线客服; 2 微信客服消息; 3 小程序自有客服; 4 公众号h5自有客服
                                            ]  //联系商家页面
                                        ],  //商家信息
                                        'payment_method'=>1,  //订单支付方式，0：未知方式 1：微信支付 2：其他支付方式
                                        'user_open_id'=>$result['openid'],  //用户的openid，参见openid说明
                                        'order_detail_page'=>[
                                            'path'=>'pages/orderItem/orderItem/orderItem?order_sn='.$result['out_trade_no']  //小程序订单详情页跳转链接
                                        ],  //订单详情页（小程序页面）
                                    ];  //订单扩展信息
                                    foreach ($goodsData as $gk=>$gv){
                                        if ($gv['order_group_sn'] == $result['out_trade_no']){
                                            $categoryList = [];
                                            foreach ($categoryData as $ck=>$cv){
                                                if ($gv['m_category_id'] == $cv['id']){
                                                    $categoryList[] = $cv['name'];
                                                    foreach ($categoryData as $pk => $pv){
                                                        if ($pv['id'] == $cv['parent_id']){
                                                            $categoryList[] = $pv['name'];
                                                        }
                                                    }
                                                }
                                            }
                                            $categoryList = array_reverse($categoryList);
                                            $circleData[0]['ext_info']['product_info']['item_list'][] = [
                                                'item_code'=>$gv['goods_id'],  //物品ID（SPU ID），要求appid下全局唯一
                                                'sku_id'=>$gv['goods_id'],  //sku_id
                                                'amount'=>$gv['number'],  //物品数量
                                                'total_fee'=>$gv['total_price']*100,  //物品总价，单位：分
                                                'thumb_url'=>$gv['pic_url'],  //物品图片，图片宽度必须大于750px，宽高比建议4:3 - 1:1之间
                                                'title'=>$gv['name'],  //物品名称
                                                'unit_price'=>$gv['price']*100,  //物品单价（实际售价），单位：分
                                                'original_price'=>$gv['price']*100,  //物品原价，单位：分
                                                'category_list'=>$categoryList,  //物品类目列表
                                                'item_detail_page'=>['path'=>'pages/goodsItem/goodsItem/goodsItem?id='.$gv['goods_id']],  //小程序物品详情页跳转链接
                                                'can_be_search'=>true
                                            ];  //物品相关信息
                                        }
                                    }
                                    $orderList['order_list'] = $circleData;
                                    $array = json_encode($orderList, JSON_UNESCAPED_UNICODE);
                                    $rs = curlPost($url, $array);
                                    file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . $rs . PHP_EOL, FILE_APPEND);
                                }
                            }
                        } else {
                            file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "未查询到订单商品信息" . PHP_EOL, FILE_APPEND);
                        }
                    }

                    //商家发货短信提醒
                    $smsModel = new SystemSmsModel();
                    $smsWhere['type'] = 1; //腾讯云
                    $smsWhere['status'] = 1;
                    $smsInfo = $smsModel->do_one($smsWhere); //查询腾讯云配置
                    $templateIdModel = new SystemSmsTemplateIdModel();
                    $templateIdInfo = $templateIdModel->do_one([]); //查询商家发货提醒短信模板id
                    if ($orderRs['data']['supplier_id'] == 0){ //查询商家电话
                        $appModel = new AppAccessModel();
                        $appInfo = $appModel->find(['key'=>$orderRs['data']['key']]);
                        if ($appInfo['status'] == 200 && !empty($appInfo['data']['phone'])){
                            $merchantPhone = $appInfo['data']['phone'];
                        }
                    }else{
                        $subUserModel = new \app\models\merchant\system\UserModel();
                        $subUserInfo = $subUserModel->find(['id'=>$orderRs['data']['supplier_id']]);
                        if ($subUserInfo['status'] == 200){
                            $supplierInfo = json_decode($subUserInfo['data']['leader'],true);
                            $merchantPhone = $supplierInfo['phone'];
                        }
                    }
                    if ($smsInfo['status'] == 200 && $templateIdInfo['status'] == 200 && isset($merchantPhone)){
                        $templateConfig = json_decode($templateIdInfo['data']['config'],true);
                        if ($templateConfig[0]['status'] == 'true'){
                            $smsAccessModel = new SystemSmsTemplateAccessModel();
                            $smsAccessWhere['phone'] = $merchantPhone;
                            $smsAccessWhere['type'] = 1; //商家发货提醒
                            $smsAccessInfo = $smsAccessModel->do_one($smsAccessWhere);
                            //离上次给商家发短信超过1小时才能再发
                            if ($smsAccessInfo['status'] == 204 || (isset($smsAccessInfo['data']) && ($smsAccessInfo['data']['create_time'] + 3600) < time())){
                                $smsInfo['data']['config'] = json_decode($smsInfo['data']['config'],true);
                                try {
                                    $sender = new SmsSingleSender($smsInfo['data']['config']['appid'], $smsInfo['data']['config']['appkey']);
                                    $sendResult = $sender->sendWithParam("86",$merchantPhone,$templateConfig[0]['templateId']);
                                    $sendRes = json_decode($sendResult, true);
                                } catch (\Exception $e) {

                                }
                                if (isset($sendRes['result']) && $sendRes['result'] == 0){
                                    $smsAccessData['phone'] = $merchantPhone;
                                    $smsAccessData['template_id'] = $templateConfig[0]['templateId'];
                                    $smsAccessData['type'] = 1; //商家发货提醒
                                    $smsAccessModel->do_add($smsAccessData);
                                }else{
                                    $sms_error['result'] = $sendRes['result'];
                                    $sms_error['errmsg'] = unicodeDecode($sendRes['errmsg']);
                                    file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . json_encode($sms_error, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
                                }
                            }else{
                                file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '一小时内只发给商家一次' . PHP_EOL, FILE_APPEND);
                            }
                        }else{
                            file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '商家发货短信提醒未开启' . PHP_EOL, FILE_APPEND);
                        }
                    }else{
                        file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '未查询到商家电话或腾讯云、短信模板配置信息' . PHP_EOL, FILE_APPEND);
                    }

                    //微信支付   佣金 库存 计算
                    //根据订单信息 减去总库存 和 各个商品库存
                    $subOrderModel = new SubOrderModel();
                    $subOrders = $subOrderModel->findall(['order_group_sn' => $result['out_trade_no']]);
                    $number = 0;

                    for ($i = 0; $i < count($subOrders['data']); $i++) {
                        if ($subOrders['data'][$i]['is_flash_sale'] == 0) {
                            $stockModel = new StockModel();
                            $number = (int)$subOrders['data'][$i]['number'];
                            $stockdata["number = number-{$number}"] = NULL;
                            $stockdata['id'] = $subOrders['data'][$i]['stock_id'];
                            $stockModel->update($stockdata);
                            $goodModel = new GoodsModel();
                            $gooddata["stocks= stocks-{$subOrders['data'][$i]['number']}"] = null;
                            $gooddata['id'] = $subOrders['data'][$i]['goods_id'];
                            $goodModel->update($gooddata);
                        } else {
                            $flashModel = new \app\models\spike\FlashSaleModel();
                            $flashGoods = $flashModel->do_one(['goods_id' => $subOrders['data'][$i]['goods_id']]);
                            $property = explode("-", $flashGoods['data']['property']);
                            $str = "";
                            $number = (int)$subOrders['data'][$i]['number'];
                            for ($j = 0; $j < count($property); $j++) {
                                $a = json_decode($property[$j], true);
                                if ($a['stock_id'] == $subOrders['data'][$i]['stock_id']) {
                                    $a['stocks'] = $a['stocks'] - $number;
                                }
                                if ($j == 0) {
                                    $str = json_encode($a, JSON_UNESCAPED_UNICODE);
                                } else {
                                    $str = $str . "-" . json_encode($a, JSON_UNESCAPED_UNICODE);
                                }
                            }
                            $flashModel->do_update(['goods_id' => $subOrders['data'][$i]['goods_id']], ['property' => $str]);
                        }

                        $goodsModel = new \app\models\shop\SaleGoodsModel();
                        $goods = $goodsModel->do_one(['id' => $subOrders['data'][$i]['goods_id']]);
                        if ($goods['data']['supplier_id'] != 0) {
                            $systemSubUserBalanceModel = new \app\models\system\SystemSubAdminBalanceModel();
                            $data = array(
                                'key' => $goods['data']['key'],
                                'merchant_id' => $goods['data']['merchant_id'],
                                'sub_admin_id' => $goods['data']['supplier_id'],
                                'order_sn' => $result['out_trade_no'],
                                'money' => $goods['data']['supplier_money'],
                                'content' => "供应商商品出售佣金",
                                'status' => 1,
                                'type' => 6,
                            );
                            $systemSubUserBalanceModel->do_add($data);
                        }
                    }


                    $payModel = new PayModel;
                    $paydata = array(
                        'transaction_id' => $result['transaction_id'],
                        'order_id' => $out_trade_no,
                        'remain_price' => $result['total_fee']/100,
                        'total_price' => $result['total_fee']/100,
                        'pay_time' => time(),
                        'status' => 1,
                        'merchant_id' => $orderRs['data']['merchant_id'],
                        'user_id' => $orderRs['data']['user_id'],
                        'update_time' => time(),
                    );

                    $res = $payModel->update($paydata);
                    $wxModel = new WeixinModel();
                    $result['wx_appid'] = $result['appid'];
                    $result['wx_mchId'] = $result['mch_id'];

                    unset($result['appid']);
                    unset($result['wx_mchId']);

                    //佣金计算 根据比例计算
                    $configModel = new \app\models\tuan\ConfigModel();
                    $con = $configModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'key' => $orderRs['data']['key']]);
                    if ($con['status'] == 200 && $con['data']['status'] == 1) {

                        if ($orderRs['data']['express_type'] == 2 && $orderRs['data']['express_price'] > 0&&$orderRs['data']['supplier_id']==0) {
                            $balanceModel = new \app\models\shop\BalanceModel;
                            $data['order_sn'] = $orderRs['data']['order_sn'];
                            $data['key'] =$orderRs['data']['key'];
                            $data['merchant_id'] = $orderRs['data']['merchant_id'];
                            $data['money'] = $orderRs['data']['express_price'];
                            $data['type'] = 6;
                            $data['uid'] = $orderRs['data']['leader_uid'];
                            $data['content'] = "配送费佣金";
                            $balanceModel->do_add($data);
                        }

                        $balance = $this->balance($orderRs['data']['order_sn'], $con['data']['commission_leader_ratio'], 0);
                        $data = array(
                            'uid' => $orderRs['data']['leader_uid'],
                            'order_sn' => $orderRs['data']['order_sn'],
                            'money' => $balance[0],
                            'content' => "团员消费",
                            'type' => 1,
                            'status' => 0
                        );
                        $data['key'] = $orderRs['data']['key'];
                        $data['merchant_id'] = $orderRs['data']['merchant_id'];
                        $balanceModel = new \app\models\shop\BalanceModel;

                        $array = $balanceModel->do_add($data);
                        $sql = "update shop_order_group set  leader_money = {$balance[0]} where order_sn = {$orderRs['data']['order_sn']}";
                        Yii::$app->db->createCommand($sql)->execute();
                    }
                }
                $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
                $comboAccessData = $comboAccessModel->do_one(['<>' => ['order_remain_number', 0], '>' => ['validity_time', time()], 'merchant_id' => $orderRs['data']['merchant_id']]);

                $comboAccessModel->do_update(['id' => $comboAccessData['data']['id']], ['order_remain_number' => $comboAccessData['data']['order_remain_number'] - 1]);


            }
            $systemPayWeixin = new WeixinModel();
            $result['status'] = 1;
            $result['create_time'] = time();

            $rs = $systemPayWeixin->add($result);

            if ($rs['status'] == 200) {
                ob_clean();
                echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                die();
            } else {
                ob_clean();
                echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
                die();
            }
        }
    }


    /**
     * 退款接口回调
     */
    public function actionNotifyreturn() {
        //获取商户微信配置
        $xml = file_get_contents("php://input");

        $wxPatNotify = new \WxPayNotify();
        $wxPatNotify->Handle(false);
        $returnValues = $wxPatNotify->GetValues();
        $result = $wxPatNotify->FromXml($xml);

        if (!empty($result['return_code']) && $result['return_code'] == 'SUCCESS') {
            $wxmodel = new SystemWxConfigModel();
            $res = $wxmodel->find(['app_id' => $result['appid']]);
            if ($res['status'] != 200) {
                $res = $wxmodel->find(['miniprogram_id' => $result['appid']]);
            }
            if ($res['status'] != 200) {
                return false;
            }
            $app = Factory::payment(json_decode($res['data']['miniprogram_pay'], true));

            $response = $app->handleRefundedNotify(function ($message, $reqInfo, $fail) {
                $model = new OrderModel();
                // 其中 $message['req_info'] 获取到的是加密信息
                // $reqInfo 为 message['req_info'] 解密后的信息
                if ($reqInfo['refund_status'] == 'SUCCESS') {
                    $data['status'] = 4;
                    $data['transaction_order_sn'] = $reqInfo['out_trade_no'];
                    $data['refund'] = json_encode($reqInfo);
                    $res = $model->update($data);
                    if ($res['status'] == 200) { // 如果订单不存在 或者 订单已经退过款了
                        $voucherModel = new \app\models\shop\VoucherModel();
                        $where['order_sn'] = $reqInfo['out_trade_no'];
                        $where['status'] = 0;
                        $voucherModel->update($where);
                        return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
                    }
                } else {
                    $data['order_sn'] = $reqInfo['out_trade_no'];
                    $data['refund'] = json_encode($reqInfo);
                    $res = $model->update($data);
                    return true;
                }
                return true; // 返回 true 告诉微信“我已处理完成”                // 或返回错误原因 $fail('参数格式校验错误');
            });
        }
    }

    /**
     * @param $log_content
     */
    private function logger($log_content) {
        if (isset($_SERVER['HTTP_APPNAME'])) {   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        } else if ($_SERVER['REMOTE_ADDR'] != "127.0.0.1") { //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if (file_exists($log_filename) and ( abs(filesize($log_filename)) > $max_size)) {
                unlink($log_filename);
            }
            file_put_contents($log_filename, date('Y-m-d H:i:s') . " " . $log_content . "\r\n", FILE_APPEND);
        }
    }

    public function actionQrcode() {
        error_reporting(E_ERROR);
        require_once yii::getAlias('@vendor/wxpay/example/qrcode.php');
        $url = urldecode($_GET['data']);
        if (substr($url, 0, 6) == "weixin") {
            \QRcode::png($url);
        } else {
            header('HTTP/1.1 404 Not Found');
        }
    }

    public function balance($order_sn, $commission_leader_ratio, $commission_selfleader_ratio) {
        $money[0] = 0;
        $money[1] = 0;
        //根据订单查询子订单
        $orderSubModel = new SubOrderModel();
        $order = $orderSubModel->findall(['order_group_sn' => $order_sn]);
        //循环订单
        $goodsModel = new GoodsModel();
        for ($i = 0; $i < count($order['data']); $i++) {
            // $good = array(); //循环查询商品
            $good = $goodsModel->find(['id' => $order['data'][$i]['goods_id']]);
            // 判断商品是否单独设置佣金
            if ($good['data']['commission_leader_ratio'] != 0) {
                $money[0] = $money[0] + ($order['data'][$i]['payment_money'] * $good['data']['commission_leader_ratio'] / 100);
            } else {
                $money[0] = $money[0] + ($order['data'][$i]['payment_money'] * $commission_leader_ratio / 100);
            }
            if ($good['data']['commission_selfleader_ratio'] != 0) {
                $money[1] = $money[1] + ($order['data'][$i]['payment_money'] * $good['data']['commission_selfleader_ratio'] / 100);
            } else {
                $money[1] = $money[1] + ($order['data'][$i]['payment_money'] * $commission_selfleader_ratio / 100);
            }
        }

        return $money;
    }

    /**
     * 支付回调 小程序扫呗支付专用回调
     * @return array
     */
    public function actionNotifySaoBei() {
        //获取商户微信配置
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);
        if (empty($data) || !is_array($data) || !isset($data['channel_trade_no']) || empty($data['channel_trade_no'])) {
            return result(200, ["return_code" => "01", "return_msg" => "缺少数据"]); //
        }
        $data_json = \Yii::$app->redis->get($data['channel_trade_no']);
        if ($data_json) {
            return result(200, ["return_code" => "01", "return_msg" => "success"]); // 已存在 直接返回
        } else {
            // 处理业务
            if (isset($data["result_code"]) && $data["result_code"] == "01") { //表示成功
                //检测订单是否是拼团订单 是的话订单状态11
                $groupAccModel = new ShopAssembleAccessModel();
                $groupWhere['order_sn'] = $data['terminal_trace'];
                $groupInfo = $groupAccModel->one($groupWhere);
                $orderModel = new OrderModel;
               // $orderRs = $orderModel->find(['order_sn' => $data['terminal_trace']]);
                $orderRes = $orderModel->findList(['transaction_order_sn' => $data['terminal_trace']]);
                $number = 1;
                if ($orderRes['status'] == 200) {
                    $number = count($orderRes['data']);
                }
                for ($l = 0; $l < $number; $l++) {
                    $status = 1;
                    if ($groupInfo['status'] == 200) {
                        $status = 11;
                    } else {
                        if ($orderRes['data'][$l]['service_goods_status'] == 1) {
                            $status = 3;
                        }
                    }
                    $orderData = array(
                        'transaction_order_sn' => $data['terminal_trace'],
                        'status' => $status,
                    );

                    $orderRs['status'] = 200;
                    $orderRs['data'] = $orderRes['data'][$l];
                    $result['out_trade_no'] = $orderRes['data'][$l]['order_sn'];
                    //处理业务 先记录条日志

                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_success.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
                    $orderData = array(
                        'order_sn' => $data['terminal_trace'],
                        'status' => $status,
                    );

                    //将订单号放入redis队列，用计划任务计算分销分佣金额
                    $dtbData['order_sn'] = $data['terminal_trace'];
                    lpushRedis('distribution',$dtbData);

                    //微信公众号商家，新订单提醒
                    $messageData['key'] = $orderRs['data']['key'];
                    $messageData['pay_time'] = time();
                    lpushRedis('wechat_template_message',$messageData);

                    //易联云自动推送，将订单号、key放入redis队列
                    $ylyData['key'] = $orderRs['data']['key'];
                    $ylyData['supplier_id'] = $orderRs['data']['supplier_id'];
                    $ylyData['order_sn'] = $result['out_trade_no'];
                    if ($orderRs['data']['supplier_id'] == 0){
                        //非门店订单
                        lpushRedis('ylyprint',$ylyData);
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "待打印_". json_encode($ylyData) . PHP_EOL, FILE_APPEND);
                    }else{
                        //门店订单
                        lpushRedis("supplier_ylyprint",$ylyData);
                        file_put_contents(Yii::getAlias('@webroot/') . '/ylyPrint.text', date('Y-m-d H:i:s') . "待打印_". json_encode($ylyData) . PHP_EOL, FILE_APPEND);
                    }

                    //好物圈导入订单
                    //查询好物圈插件是否开启
                    $appAccessModel = new AppAccessModel();
                    $appAccessInfo = $appAccessModel->find(['`key`' => $orderRs['data']['key']]);

                    if (isset($appAccessInfo['status']) && $appAccessInfo['status'] == 200 && $appAccessInfo['data']['good_phenosphere'] == 1) {
                        $categoryModel = new MerchantCategoryModel();
                        $sql = "SELECT so.*,sg.m_category_id FROM `shop_order_group` sog LEFT JOIN `shop_order` so ON so.order_group_sn = sog.order_sn LEFT JOIN `shop_goods` sg ON sg.id = so.goods_id WHERE sog.`key` = '" . $orderRs['data']['key'] . "' AND sog.order_sn = '" . $data['terminal_trace'] . "'";
                        $goodsData = $orderModel->querySql($sql);
                        //商品分类
                        $sql = "SELECT * FROM `shop_marchant_category` WHERE `key` = '" . $orderRs['data']['key'] . "' AND delete_time is NULL";
                        $categoryData = $categoryModel->querySql($sql);
                        if (count($goodsData) > 0 || count($categoryData) > 0) {
                            $config = $this->getSystemConfig($orderRs['data']['key'], "miniprogram");
                            if ($config == false) {
                                file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "未配置小程序信息" . PHP_EOL, FILE_APPEND);
                            } else {
                                $miniProgram = Factory::miniProgram($config);
                                $token = $miniProgram->access_token->getToken(true);// 强制重新从微信服务器获取 token
                                if (!isset($token['access_token'])) {
                                    file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "小程序access_token不存在" . PHP_EOL, FILE_APPEND);
                                } else {
                                    $miniProgram['access_token']->setToken($token['access_token'], 3600);
                                    $access_token = $token['access_token'];
                                    $url = "https://api.weixin.qq.com/mall/importorder?action=add-order&is_history=0&access_token={$access_token}";
                                    $circleData[0]['order_id'] = $data['terminal_trace'];  //订单id，需要保证唯一性
                                    $circleData[0]['create_time'] = $orderRs['data']['create_time'];  //订单创建时间，unix时间戳
                                    $circleData[0]['pay_finish_time'] = time();  //支付完成时间，unix时间戳
                                    $circleData[0]['trans_id'] = $data['channel_trade_no'];  //微信支付订单id，对于使用微信支付的订单，该字段必填
                                    $circleData[0]['fee'] = $data['total_fee'];  //订单金额，单位：分
                                    $circleData[0]['status'] = 3;//订单状态，3：支付完成 4：已发货 5：已退款 100: 已完成
                                    $circleData[0]['ext_info'] = [
                                        'express_info' => [
                                            'price' => $orderRs['data']['express_price'] * 100 //运费，单位：分
                                        ],  //快递信息
                                        'brand_info' => [
                                            'contact_detail_page' => [
                                                'kf_type' => 1   //在线客服类型 1 没有在线客服; 2 微信客服消息; 3 小程序自有客服; 4 公众号h5自有客服
                                            ]  //联系商家页面
                                        ],  //商家信息
                                        'payment_method' => 1,  //订单支付方式，0：未知方式 1：微信支付 2：其他支付方式
                                        'user_open_id' => $data['user_id'],  //用户的openid，参见openid说明
                                        'order_detail_page' => [
                                            'path' => 'pages/orderItem/orderItem/orderItem?order_sn=' . $data['terminal_trace']  //小程序订单详情页跳转链接
                                        ],  //订单详情页（小程序页面）
                                    ];  //订单扩展信息
                                    foreach ($goodsData as $gk => $gv) {
                                        if ($gv['order_group_sn'] == $data['terminal_trace']) {
                                            $categoryList = [];
                                            foreach ($categoryData as $ck => $cv) {
                                                if ($gv['m_category_id'] == $cv['id']) {
                                                    $categoryList[] = $cv['name'];
                                                    foreach ($categoryData as $pk => $pv) {
                                                        if ($pv['id'] == $cv['parent_id']) {
                                                            $categoryList[] = $pv['name'];
                                                        }
                                                    }
                                                }
                                            }
                                            $categoryList = array_reverse($categoryList);
                                            $circleData[0]['ext_info']['product_info']['item_list'][] = [
                                                'item_code' => $gv['goods_id'],  //物品ID（SPU ID），要求appid下全局唯一
                                                'sku_id' => $gv['goods_id'],  //sku_id
                                                'amount' => $gv['number'],  //物品数量
                                                'total_fee' => $gv['total_price'] * 100,  //物品总价，单位：分
                                                'thumb_url' => $gv['pic_url'],  //物品图片，图片宽度必须大于750px，宽高比建议4:3 - 1:1之间
                                                'title' => $gv['name'],  //物品名称
                                                'unit_price' => $gv['price'] * 100,  //物品单价（实际售价），单位：分
                                                'original_price' => $gv['price'] * 100,  //物品原价，单位：分
                                                'category_list' => $categoryList,  //物品类目列表
                                                'item_detail_page' => ['path' => 'pages/goodsItem/goodsItem/goodsItem?id=' . $gv['goods_id']],  //小程序物品详情页跳转链接
                                                'can_be_search' => true
                                            ];  //物品相关信息
                                        }
                                    }
                                    $orderList['order_list'] = $circleData;
                                    $array = json_encode($orderList, JSON_UNESCAPED_UNICODE);
                                    $rs = curlPost($url, $array);
                                    file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . $rs . PHP_EOL, FILE_APPEND);
                                }
                            }
                        } else {
                            file_put_contents(Yii::getAlias('@webroot/') . '/circle.text', date('Y-m-d H:i:s') . "未查询到订单商品信息" . PHP_EOL, FILE_APPEND);
                        }
                    }

                    //商家发货短信提醒
                    $smsModel = new SystemSmsModel();
                    $smsWhere['type'] = 1; //腾讯云
                    $smsWhere['status'] = 1;
                    $smsInfo = $smsModel->do_one($smsWhere); //查询腾讯云配置
                    $templateIdModel = new SystemSmsTemplateIdModel();
                    $templateIdInfo = $templateIdModel->do_one([]); //查询商家发货提醒短信模板id
                    if ($orderRs['data']['supplier_id'] == 0){ //查询商家电话
                        $appModel = new AppAccessModel();
                        $appInfo = $appModel->find(['key'=>$orderRs['data']['key']]);
                        if ($appInfo['status'] == 200 && !empty($appInfo['data']['phone'])){
                            $merchantPhone = $appInfo['data']['phone'];
                        }
                    }else{
                        $subUserModel = new \app\models\merchant\system\UserModel();
                        $subUserInfo = $subUserModel->find(['id'=>$orderRs['data']['supplier_id']]);
                        if ($subUserInfo['status'] == 200){
                            $supplierInfo = json_decode($subUserInfo['data']['leader'],true);
                            $merchantPhone = $supplierInfo['phone'];
                        }
                    }
                    if ($smsInfo['status'] == 200 && $templateIdInfo['status'] == 200 && isset($merchantPhone)){
                        $templateConfig = json_decode($templateIdInfo['data']['config'],true);
                        if ($templateConfig[0]['status'] == 'true'){
                            $smsAccessModel = new SystemSmsTemplateAccessModel();
                            $smsAccessWhere['phone'] = $merchantPhone;
                            $smsAccessWhere['type'] = 1; //商家发货提醒
                            $smsAccessInfo = $smsAccessModel->do_one($smsAccessWhere);
                            //离上次给商家发短信超过1小时才能再发
                            if ($smsAccessInfo['status'] == 204 || (isset($smsAccessInfo['data']) && ($smsAccessInfo['data']['create_time'] + 3600) < time())){
                                $smsInfo['data']['config'] = json_decode($smsInfo['data']['config'],true);
                                try {
                                    $sender = new SmsSingleSender($smsInfo['data']['config']['appid'], $smsInfo['data']['config']['appkey']);
                                    $sendResult = $sender->sendWithParam("86",$merchantPhone,$templateConfig[0]['templateId']);
                                    $sendRes = json_decode($sendResult, true);
                                } catch (\Exception $e) {

                                }
                                if (isset($sendRes['result']) && $sendRes['result'] == 0){
                                    $smsAccessData['phone'] = $merchantPhone;
                                    $smsAccessData['template_id'] = $templateConfig[0]['templateId'];
                                    $smsAccessData['type'] = 1; //商家发货提醒
                                    $smsAccessModel->do_add($smsAccessData);
                                }else{
                                    $sms_error['result'] = $sendRes['result'];
                                    $sms_error['errmsg'] = unicodeDecode($sendRes['errmsg']);
                                    file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . json_encode($sms_error, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
                                }
                            }else{
                                file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '一小时内只发给商家一次' . PHP_EOL, FILE_APPEND);
                            }
                        }else{
                            file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '商家发货短信提醒未开启' . PHP_EOL, FILE_APPEND);
                        }
                    }else{
                        file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '未查询到商家电话或腾讯云、短信模板配置信息' . PHP_EOL, FILE_APPEND);
                    }

                    try {
                        $tr = Yii::$app->db->beginTransaction();
                        $orderData['commission'] = $this->fenxiao($data['terminal_trace'], $appAccessInfo['data']['distribution']);
                        $res = $orderModel->update($orderData);
                        if ($orderRs['data']['order_type'] == 2) {
                            $shopUserModel = new \app\models\shop\UserModel;
                            $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);

                        }
                        //根据订单信息 减去总库存 和 各个商品库存
                        $subOrderModel = new SubOrderModel();
                        $subOrders = $subOrderModel->findall(['order_group_sn' => $data['terminal_trace']]);
                        for ($i = 0; $i < count($subOrders['data']); $i++) {
                            $stockModel = new StockModel();
                            $number = (int)$subOrders['data'][$i]['number'];
                            $stockdata["number = number-{$number}"] = NULL;
                            $stockdata['id'] = $subOrders['data'][$i]['stock_id'];
                            $res = $res && $stockModel->update($stockdata);
                            $goodModel = new GoodsModel();
                            $gooddata["stocks= stocks-{$subOrders['data'][$i]['number']}"] = null;
                            $gooddata['id'] = $subOrders['data'][$i]['goods_id'];
                            $res = $res && $goodModel->update($gooddata);
                        }

                        $payModel = new PayModel;
                        $paydata = array(
                            'transaction_id' => $data['channel_trade_no'],
                            'order_id' => $data['terminal_trace'],
                            'remain_price' => $data['total_fee'],
                            'total_price' => $data['total_fee'],
                            'pay_time' => time(),
                            'status' => 1,
                            'merchant_id' => $orderRs['data']['merchant_id'],
                            'user_id' => $orderRs['data']['user_id'],
                            'update_time' => time(),
                        );
                        $res = $res && $payModel->update($paydata);
                        /*  $wxModel = new WeixinModel();
                          $result['wx_appid'] = $result['appid'];
                          $result['wx_mchId'] = $result['mch_id']; */

                        //佣金计算 根据比例计算
                        $configModel = new \app\models\tuan\ConfigModel();

                        $con = $configModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'key' => $orderRs['data']['key']]);
                        if ($con['status'] == 200 && $con['data']['status'] == 1) {
                            $tuanUserModel = new \app\models\tuan\UserModel;
                            $tuanUser = $tuanUserModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'uid' => $orderRs['data']['user_id']]);

                            if ($tuanUser['status'] == 204) {
                                $tuanData = array(
                                    'key' => $orderRs['data']['key'],
                                    'merchant_id' => $orderRs['data']['merchant_id'],
                                    'uid' => $orderRs['data']['user_id'],
                                    'leader_uid' => $orderRs['data']['leader_self_uid'],
                                    'status' => 1,
                                );
                                $res = $res && $tuanUserModel->do_add($tuanData);
                                $tuanUser = $tuanUserModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'uid' => $orderRs['data']['user_id']]);
                            }
                            if ($orderRs['data']['express_type'] == 2 && $orderRs['data']['express_price'] != 0) {
                                $leaderModel = new \app\models\tuan\LeaderModel();
                                $leader = $leaderModel->do_one(['uid' => $orderRs['data']['leader_self_uid']]);

                                $balanceModel = new \app\models\shop\BalanceModel;
                                $data_ba_['money'] = $leader['data']['tuan_express_fee'];
                                $data_ba_['type'] = 6;
                                $data_ba_['uid'] = $orderRs['data']['leader_self_uid'];
                                $data_ba_['content'] = "配送费佣金";
                                $res = $res && $balanceModel->do_add($data_ba_);
                            }
                            $balance = $this->balance($data['terminal_trace'], $con['data']['commission_leader_ratio'], $con['data']['commission_selfleader_ratio']);
                            $data_ba = array(
                                'uid' => $tuanUser['data']['leader_uid'],
                                'order_sn' => $data['terminal_trace'],
                                'money' => $balance[0],
                                'content' => "团员消费",
                                'type' => 1,
                                'status' => 0
                            );
                            $data_ba['key'] = $orderRs['data']['key'];
                            $data_ba['merchant_id'] = $orderRs['data']['merchant_id'];
                            $sql = "update shop_order_group set  leader_money = {$balance[0]} where order_sn = {$orderRs['data']['terminal_trace']}";
                            Yii::$app->db->createCommand($sql)->execute();
                            $balanceModel = new \app\models\shop\BalanceModel;
                            if ($orderRs['data']['leader_self_uid'] != 0) {
                                $data_ba['money'] = $balance[0];
                                $data_ba['type'] = 3;
                                $data_ba['uid'] = $orderRs['data']['leader_self_uid'];
                                $data_ba['content'] = "团员消费";
                                $res = $res && $balanceModel->do_add($data_ba);
                            }
                        }
                        $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
                        $comboAccessData = $comboAccessModel->do_one(['<>' => ['order_remain_number', 0], '>' => ['validity_time', time()], 'merchant_id' => $orderRs['data']['merchant_id']]);

                        $res = $res && $comboAccessModel->do_update(['id' => $comboAccessData['data']['id']], ['order_remain_number' => $comboAccessData['data']['order_remain_number'] - 1]);
                        if ($res && \Yii::$app->redis->set($data["channel_trade_no"], "1")) {
                            $tr->commit();
                            return result(200, ["return_code" => "01", "return_msg" => "success"]);
                        }
                        $tr->rollBack();
                        return result(200, ["return_code" => "02", "return_msg" => "error"]);
                    } catch (\Exception $e) {
                        file_put_contents(Yii::getAlias('@webroot/') . '/pay_error_1.text', date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL, FILE_APPEND);
                    }
                }
            } else {
                // 错误处理 记录日志
                file_put_contents(Yii::getAlias('@webroot/') . '/pay_error.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
            }
        }
    }


    public  function fenxiao($order_sn,$distribution){
        $orderSubModel = new SubOrderModel();
        $order = $orderSubModel->findall(['order_group_sn' => $order_sn]);
        if($distribution==""||$distribution==null){
            $distribution = 0;
        }else{
            $a = json_decode($distribution,true);
            $distribution = $a['total'];
        }
        //循环订单
        $money = 0;
        $goodsModel = new GoodsModel();
        for ($i = 0; $i < count($order['data']); $i++) {
            // $good = array(); //循环查询商品
            $good = $goodsModel->find(['id' => $order['data'][$i]['goods_id']]);
            // 判断商品是否单独设置佣金
            if ((int)$good['data']['distribution'] != 0) {
                $money = $money + (($order['data'][$i]['payment_money']-$order['data'][$i]['express_price']) * $good['data']['distribution'] / 100);
            } else {
                $money= $money + ($order['data'][$i]['payment_money'] * $distribution / 100);
            }
        }
        return $money;
    }
}
