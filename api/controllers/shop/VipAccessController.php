<?php

namespace app\controllers\shop;

use app\models\merchant\vip\UnpaidVipModel;
use app\models\merchant\vip\VipModel;
use app\models\shop\GroupOrderModel;
use app\models\shop\VipAccessModel;
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
 * 会员卡订单类
 * @author wmy
 * Class VipAccessController
 * @package app\controllers\shop
 */
class VipAccessController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 检测是否是vip
     * @return array
     * @throws yii\db\Exception
     */
    public function actionIsVip(){
        if (yii::$app->request->isGet) {
            $userModel = new UserModel();
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = yii::$app->session['key'];
            $where['id'] = yii::$app->session['user_id'];
            $info = $userModel->find($where);
            if($info['status'] != 200){
                return result(500, "未找到数据");
            }
            if($info['data']['vip_validity_time'] < time()){
                $result['is_vip'] = 0;
                $result['end_time'] = '';
            }else{
                $result['is_vip'] = $info['data']['is_vip'];
                $result['end_time'] = date('Y年m月d日', (int)$info['data']['vip_validity_time']);
            }
            return result(200, "请求成功",$result);
        }else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 查询会员卡列表
     * @return array
     */
    public function actionVipList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new VipModel();
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['key'] = yii::$app->session['key'];
            $params['status'] = 1;
            $array = $model->do_select($params);
            if($array['status'] == 200){
                foreach ($array['data'] as $key=>&$val){
                    if((int)$val['validity_time'] == 86400*7){
                        $val['validity_time'] = 1;
                        $val['validity_time_text'] = '一周';
                    }elseif ((int)$val['validity_time'] == 86400*30){
                        $val['validity_time'] = 2;
                        $val['validity_time_text'] = '一个月';
                    }elseif ((int)$val['validity_time'] == 86400*90){
                        $val['validity_time'] = 3;
                        $val['validity_time_text'] = '一个季度';
                    }elseif ((int)$val['validity_time'] == 86400*365){
                        $val['validity_time'] = 4;
                        $val['validity_time_text'] = '一年';
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


    /**
     * vip订单列表
     * @return array
     */
    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new VipAccessModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            if(isset($params['key'])){
                unset($params['key']);
            }
            $array = $model->vip_order($params);
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
            if(!isset($params['vip_id'])){
                return result(500, "缺少参数！");
            }
            //检测此vip是否还存在
            $vipModel = new VipModel();
            $where['`key`'] = yii::$app->session['key'];
            $where['merchant_id'] = yii::$app->session['merchant_id'];
            $where['id'] = $params['vip_id'];
            $where['status'] = 1;
            $info = $vipModel->one($where);
            if($info['status'] != 200 || $info['data']['money'] <= 0){
                return result(500, "不存在的会员卡");
            }
            $vipAccessModel = new VipAccessModel();
            if($info['data']['pay_count'] != 0){ //购买次数不等于0
                //检测当前用户是否购买过
                $where_['key'] = yii::$app->session['key'];
                $where_['status'] = 1;
                $where_['merchant_id'] = yii::$app->session['merchant_id'];
                $where_['user_id'] = yii::$app->session['user_id'];
                $where_['vip_id'] = $params['vip_id'];
                $total = $vipAccessModel->get_count($where_);
                if((int)$total - $info['data']['pay_count'] >= 0){ //已经购买过
                    return result(500, "此会员卡只能购买".$info['data']['pay_count']."次");
                }
            }
            $pay_sn = date('YmdHis') . str_pad(mt_rand(2019, 9999), 4, '2019', STR_PAD_LEFT);
            $order = array(
                'key' => yii::$app->session['key'],
                'merchant_id' => yii::$app->session['merchant_id'],
                'user_id' => yii::$app->session['user_id'],
                'vip_id' => $params['vip_id'],
                'money' => $info['data']['money'],
                'validity_time' => $info['data']['validity_time'],
                'status' => 0,
                'pay_sn' => $pay_sn
            );
            try {
                //提交订单
                $res = $vipAccessModel->add($order);
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
     * 会员卡去付款
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
            $vipAccessModel = new VipAccessModel();
            $where['pay_sn'] = $id;
            $where['`key`'] =  yii::$app->session['key'];
            $where['merchant_id'] =  yii::$app->session['merchant_id'];
            $where['user_id'] =  yii::$app->session['user_id'];
            $accessInfo = $vipAccessModel->one($where);
            if ($accessInfo['status'] != 200) {
                return result(500, "订单信息无效！");
            }
            if ($params['pay_type'] == 1) {
                $config = $this->getSystemConfig(yii::$app->session['key'], "wxpay");
                if ($config == false) {
                    return result(500, "未配置微信信息");
                }
            } else {
                $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogrampay");
                if ($config == false) {
                    return result(500, "未配置小程序信息");
                }
            }

            $vipModel = new VipModel();
            $vipWhere['id'] = $accessInfo['data']['vip_id'];
            $vipInfo = $vipModel->one($vipWhere);
            if (count($vipInfo['data']) > 1) {
                $name = $vipInfo['data']['name'];
            } else {
                $name = '未知';
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
            $vipAccessModel->do_update(['pay_sn' => $id,'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id']], ['pay_type' => $pay_type]);
            if ($config['wx_pay_type'] == 1) { // 微信支付
                $payment = Factory::payment($config);
                $wxPayData = array(
                    'body' => $name,
                    'attach' => 'vip',
                    'out_trade_no' => $id,
                    'total_fee' => $accessInfo['data']['money'] * 100,
                    'notify_url' => "http://" . $_SERVER['HTTP_HOST'] . "/api/web/index.php/pay/vip-access/notify",  //回调地址
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
                $mini_pay->setNotify_url("http://" . $_SERVER['HTTP_HOST'] . "/api/web/index.php/pay/vip-access/notify-sao-bei");  //回调地址
                $pay_pre = Payx::miniPayRe($mini_pay, $config['saobei_access_token']);
                if ($pay_pre->return_code == "01") {
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

    /**
     * 查询积分会员卡信息
     * @return array
     * @throws yii\db\Exception
     */
    public function actionUnpaidVip() {
        if (yii::$app->request->isGet) {
            $model = new UnpaidVipModel();
            $where['key'] = yii::$app->session['key'];
            $where['merchant_id'] = yii::$app->session['merchant_id'];
            $where['limit'] = false;
            $res = $model->do_select($where);

            $orderModel = new GroupOrderModel();
            $orderWhere['user_id'] = yii::$app->session['user_id'];
            $orderWhere['or'] = ['or',['=','status',6],['=','status',7],['=','status',3]];
            $orderWhere['limit'] = false;
            $orderWhere['field'] = 'sum(payment_money) as payment_money';
            $orderInfo = $orderModel->do_select($orderWhere);
            $pay_price = 0;
            if ($orderInfo['status'] == 200){
                $pay_price = $orderInfo['data'][0]['payment_money'] == null ? 0 : $orderInfo['data'][0]['payment_money'];
            }

            if ($res['status'] == 200){
                $minLev = reset($res['data']);//最低等级
                $maxLev = end($res['data']);//最高等级
                //总积分小于最低等级时
                $array = [];
                if ($pay_price < $minLev['min_score']){
                    $array['info']['min_score'] = intval($pay_price);
                    $array['info']['name'] = "无等级";
                    $array['info']['discount_ratio'] = 1;
                    $array['next']['min_score'] = $minLev['min_score'];
                    $array['next']['name'] = $minLev['name'];
                }
                //总积分大于等于最高等级
                if ($pay_price >= $maxLev['min_score']){
                    $array['up']['min_score'] = $maxLev['min_score'];
                    $array['up']['name'] = $maxLev['name'];
                    $array['info']['min_score'] = intval($pay_price);
                    $array['info']['name'] = $maxLev['name'];
                    $array['info']['discount_ratio'] = $maxLev['discount_ratio'];
                }
                //总积分在最低和最高之间的
                if ($pay_price >= $minLev['min_score'] && $pay_price < $maxLev['min_score']){
                    foreach ($res['data'] as $k=>$v){
                        if ($pay_price >= $v['min_score']){
                            $array['up']['min_score'] = $v['min_score'];
                            $array['up']['name'] = $v['name'];
                            $array['info']['min_score'] = intval($pay_price);
                            $array['info']['name'] = $v['name'];
                            $array['info']['discount_ratio'] = $v['discount_ratio'];
                            $array['next']['min_score'] = $res['data'][$k+1]['min_score'];
                            $array['next']['name'] = $res['data'][$k+1]['name'];
                        }
                    }
                }
                return result(200, "请求成功",$array);
            }else{
                return $res;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }


}
