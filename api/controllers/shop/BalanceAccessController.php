<?php

namespace app\controllers\shop;

use app\models\merchant\balance\BalanceConfigModel;
use app\models\shop\BalanceAccessModel;
use tools\pay\mini_pay\MiniPay;
use tools\pay\Payx;
use yii;
use app\models\shop\UserModel;
use EasyWeChat\Factory;
use yii\web\ShopController;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');
require_once yii::getAlias('@vendor/tencentyun/image/sample.php');
include dirname(dirname(__DIR__)) . '/extend/tools/pay/MiniPay/MiniPay.php';
include dirname(dirname(__DIR__)) . '/extend/tools/pay/Pay.php';

/**
 * 充值余额订单类
 * @author wmy
 * Class BalanceAccessController
 * @package app\controllers\shop
 */
class BalanceAccessController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 查询余额配置列表
     * @return array
     */
    public function actionConfigList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new BalanceConfigModel();
            $params['key'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['status'] = 1;
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


    /**
     * 余额充值订单列表
     * @return array
     */
    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new BalanceAccessModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            if(isset($params['key'])){
                unset($params['key']);
            }
            $array = $model->balance_order($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 生成订单
     */
    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new BalanceConfigModel();
            if(!isset($params['money']) && !isset($params['balance_id'])){
                return result(500, "缺少参数");
            }
            if(isset($params['balance_id']) && (int)$params['balance_id'] > 0){ // 存在余额配置id
                $where['`key`'] = yii::$app->session['key'];
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                $where['id'] = $params['balance_id'];
                $where['status'] = 1;
                $configInfo = $model->one($where);
                if($configInfo['status'] != 200){
                    return result(500, "不存在的充值配置");
                }
                $remain_money = $configInfo['data']['remain_money'];
                $money = $configInfo['data']['money'];
            }else{
                if(isset($params['money']) && (!is_numeric($params['money']) || empty($params['money']))){
                    return result(500, "充值金额只能是数字");
                }
                $remain_money = $params['money'];
                $money = $params['money'];
            }
            $pay_sn = date('YmdHis') . str_pad(mt_rand(2019, 9999), 4, '2019', STR_PAD_LEFT);
            $order = array(
                'key' => yii::$app->session['key'],
                'merchant_id' => yii::$app->session['merchant_id'],
                'user_id' => yii::$app->session['user_id'],
                'money' => $money,
                'remain_money' => $remain_money,
                'status' => 0,
                'pay_sn' => $pay_sn
            );
            try {
                $balanceAccessModel = new BalanceAccessModel();
                //提交订单
                $res = $balanceAccessModel->add($order);
                if($res['status'] == 200){
                    return result(200, '请求成功',$pay_sn);
                }
                return $res;
            } catch (\Exception $e) {
                return result(500, '数据异常');
            }
        }
    }

    /**
     * 去付款
     * @param $id
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws yii\db\Exception
     */
    public function actionPay($id) {
        if (yii::$app->request->isPost) {
            //微信内部调取支付接口
            $request = yii::$app->request; //获取 request 对象
            $params = $request->post(); //获取地址栏参数
            if((int)$id < 1){
                return result(500, "缺少参数");
            }
            $balanceAccessModel = new BalanceAccessModel();
            $where['pay_sn'] = $id;
            $where['`key`'] =  yii::$app->session['key'];
            $where['merchant_id'] =  yii::$app->session['merchant_id'];
            $where['user_id'] =  yii::$app->session['user_id'];
            $accessInfo = $balanceAccessModel->one($where);
            if ($accessInfo['status'] != 200) {
                return result(500, "订单信息无效！");
            }
            if ($params['pay_type'] == 1) {
                $config = $this->getSystemConfig(yii::$app->session['key'], "wxpay",1);
                if ($config == false) {
                    return result(500, "未配置微信信息");
                }
            } else {
                $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogrampay",1);
                if ($config == false) {
                    return result(500, "未配置小程序信息");
                }
            }
            //获取下单用户opid
            $userModel = new UserModel;
            $userData = $userModel->find(['id' => yii::$app->session['user_id']]);
            if ($userData['status'] != 200) {
                return result('500', '下单失败，找不到用户信息');
            }
            $userModel->update(['id' => yii::$app->session['user_id'], '`key`' => yii::$app->session['key'], 'money' => $userData['data']['money'] + $accessInfo['data']['money']]);
            if($config['wx_pay_type'] == 1){
                $pay_type = 1;
            }elseif ($config['wx_pay_type'] == 2){
                $pay_type = 5;
            }else{
                $pay_type = 2;
            }
            $balanceAccessModel->do_update(['pay_sn' => $id,'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id']], ['pay_type' => $pay_type]);
            if ($config['wx_pay_type'] == 1) { // 微信支付
                $payment = Factory::payment($config);
                $wxPayData = array(
                    'body' => '余额充值',
                    'attach' => 'balance',
                    'out_trade_no' => $id,
                    'total_fee' => $accessInfo['data']['money'] * 100,
                    'notify_url' => "https://".$_SERVER['HTTP_HOST']."/api/web/index.php/pay/balance-access/notify",
                    'trade_type' => 'JSAPI',
                );
                if ($params['pay_type'] == 1) {
                    $wxPayData['openid'] = $userData['data']['wx_open_id'];
                } else {
                    $wxPayData['openid'] = $userData['data']['mini_open_id'];
                }
                $rs = $payment->order->unify($wxPayData);
                if ($rs['return_code'] == "SUCCESS") {
                    $jssdk = $payment->jssdk;
                    $payinfo = $jssdk->bridgeConfig($rs['prepay_id'], false); // 返回数组
                    return result(200, "请求成功", $payinfo);
                } else {
                    return result(500, "下单失败");
                }
            } else { //扫呗支付
                $mini_pay = new MiniPay();
                $mini_pay->setPay_ver(Payx::PAY_VER);
                $mini_pay->setPay_type("010");
                $mini_pay->setService_id(Payx::SERVICE_ID);
                $mini_pay->setMerchant_no($config['merchant_no']);
                $mini_pay->setTerminal_id($config['terminal_id']);
                $mini_pay->setTerminal_trace($id);
                $mini_pay->setTerminal_time(date("YmdHis"));
                $mini_pay->setTotal_fee($accessInfo['data']['money'] * 100);
                $mini_pay->setOpen_id($userData['data']['mini_open_id']);
                $mini_pay->setNotify_url("https://".$_SERVER['HTTP_HOST']."/api/web/index.php/pay/balance-access/notify-sao-bei");
                $pay_pre = Payx::miniPayRe($mini_pay, $config['saobei_access_token']);
                if ($pay_pre->return_code == "01" && $pay_pre->result_code == "01") {
                    $saobei_payinfo = [
                        'appId' => $pay_pre->appId ?? $config['app_id'],
                        'timeStamp' => $pay_pre->timeStamp,
                        'nonceStr' => $pay_pre->nonceStr,
                        'package' => $pay_pre->package_str,
                        'signType' => $pay_pre->signType,
                        'paySign' => $pay_pre->paySign,
                    ];
                    return result(200, "请求成功", $saobei_payinfo);
                } else {
                    return result(500, "下单失败");
                }
            }
        } else {
            return result(500, "请求方式错误");
        }
    }
}
