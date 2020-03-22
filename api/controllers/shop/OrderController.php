<?php

namespace app\controllers\shop;

use app\controllers\pay\WechatController1;
use app\models\merchant\distribution\AgentModel;
use app\models\merchant\distribution\DistributionAccessModel;
use app\models\merchant\distribution\OperatorModel;
use app\models\merchant\distribution\SuperModel;
use app\models\merchant\vip\VipConfigModel;
use app\models\merchant\vip\VipModel;
use app\models\shop\GroupOrderModel;
use app\models\shop\MerchantCategoryModel;
use app\models\shop\ShopAssembleAccessModel;
use app\models\shop\ShopAssembleModel;
use app\models\shop\ShopBargainInfoModel;
use app\models\shop\VipAccessModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\system\SystemWxConfigModel;
use app\models\tuan\LeaderModel;
use tools\pay\mini_pay\MiniPay;
use tools\pay\Payx;
use yii;
use yii\base\Exception;
use yii\web\ShopController;
use app\models\shop\StockModel;
use app\models\shop\GoodsModel;
use app\models\shop\CashbackModel;
use app\models\shop\VoucherModel;
use app\models\shop\ContactModel;
use app\models\shop\OrderModel;
use app\models\shop\SubOrderModel;
use app\models\core\TableModel;
use app\models\core\CosModel;
use app\models\shop\UserModel;
use EasyWeChat\Factory;
use app\models\shop\ShopExpressTemplateDetailsModel;
use app\models\merchant\pay\PayModel;
use app\models\shop\CartModel;
use app\models\core\UploadsModel;
use app\models\shop\ShopExpressTemplateModel;
use app\models\shop\ScoreModel;
use app\models\admin\app\AppAccessModel;
use app\models\admin\system\SystemCosModel;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');
require_once yii::getAlias('@vendor/tencentyun/image/sample.php');
include dirname(dirname(__DIR__)) . '/extend/tools/pay/MiniPay/MiniPay.php';
include dirname(dirname(__DIR__)) . '/extend/tools/pay/Pay.php';
include dirname(dirname(__DIR__)) . '/extend/tools/pay/Refund/Refund.php';

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class OrderController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new OrderModel();
            $model->timeOutOrder();
            $params['shop_order_group.`key`'] = yii::$app->session['key'];
            $params['shop_order_group.merchant_id'] = yii::$app->session['merchant_id'];
            $params['shop_order_group.user_id'] = yii::$app->session['user_id'];
            $array = $model->shop_order($params);

            $leaderModel = new \app\models\tuan\LeaderModel();
            $leaderWhere['shop_tuan_leader.key'] = yii::$app->session['key'];
            $leaderWhere['.shop_tuan_leader.merchant_id'] = yii::$app->session['merchant_id'];
            $leaderWhere['field'] = "shop_tuan_leader.*,shop_user.phone,shop_user.avatar";
            $leaderWhere['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_tuan_leader.uid'];
            $leaderWhere['limit'] = false;
            $leaders = $leaderModel->do_select($leaderWhere);

            if ($array['status'] == 200 && $leaders['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {

                    $array['data'][$i]['leader'] = array();
                    for ($j = 0; $j < count($leaders['data']); $j++) {
                        if ($array['data'][$i]['leader_self_uid'] == $leaders['data'][$j]['uid']) {
                            $areaModel = new \app\models\system\SystemAreaModel();
                            $province = $areaModel->do_column(['field' => 'name', 'code' => $leaders['data'][$j]['province_code']]);
                            $city = $areaModel->do_column(['field' => 'name', 'code' => $leaders['data'][$j]['city_code']]);
                            $area = $areaModel->do_column(['field' => 'name', 'code' => $leaders['data'][$j]['area_code']]);
                            $leaders['data'][$j]['province'] = $province['data'][0];
                            $leaders['data'][$j]['city'] = $city['data'][0];
                            $leaders['data'][$j]['area'] = $area['data'][0];
                            $array['data'][$i]['leader'] = $leaders['data'][$j];
                        }
                    }
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new OrderModel();

            $data['`key`'] = yii::$app->session['key'];
            $data['order_sn'] = $id;
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $array = $model->one($data);
            if ($array['status'] == 200) {
                $payModel = new PayModel();
                $payData = $payModel->find(['order_id' => $id, 'type' => 3]);
                if ($payData['status'] == 200) {
                    $array['data']['weixinOrder']['transaction_id'] = $payData['data']['transaction_id'];
                    $array['data']['weixinOrder']['pay_time'] = isset($payData['data']['pay_time']) ? date('Y-m-d H:i:s', $payData['data']['pay_time']) : "";
                } else {
                    $array['data']['weixinOrder']['transaction_id'] = "";
                    $array['data']['weixinOrder']['pay_time'] = "";
                }
            }
            $leaderModel = new \app\models\tuan\LeaderModel();
            $leaderWhere['shop_tuan_leader.key'] = yii::$app->session['key'];
            $leaderWhere['.shop_tuan_leader.merchant_id'] = yii::$app->session['merchant_id'];
            $leaderWhere['field'] = "shop_tuan_leader.*,shop_user.phone,shop_user.avatar";
            $leaderWhere['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_tuan_leader.uid'];
            $leaders = $leaderModel->do_select($leaderWhere);

            if ($array['status'] == 200 && $leaders['status'] == 200) {
                $array['data']['leader'] = array();
                for ($j = 0; $j < count($leaders['data']); $j++) {
                    if ($array['data']['leader_self_uid'] == $leaders['data'][$j]['uid']) {
                        $areaModel = new \app\models\system\SystemAreaModel();
                        $province = $areaModel->do_column(['field' => 'name', 'code' => $leaders['data'][$j]['province_code']]);
                        $city = $areaModel->do_column(['field' => 'name', 'code' => $leaders['data'][$j]['city_code']]);
                        $area = $areaModel->do_column(['field' => 'name', 'code' => $leaders['data'][$j]['area_code']]);
                        $leaders['data'][$j]['province'] = $province['data'][0];
                        $leaders['data'][$j]['city'] = $city['data'][0];
                        $leaders['data'][$j]['area'] = $area['data'][0];
                        $array['data']['leader'] = $leaders['data'][$j];
                    }
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionExpress($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new OrderModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $data['order_sn'] = $id;
            $array = $model->express($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 生成订单  shopOrder 商城订单 tuanOrder团购订单
     */
    public function actionAdd()
    {

        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $tuanConfigModel = new \app\models\tuan\ConfigModel();
            $tuanconfig = $tuanConfigModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);
            if (isset($params['group_type']) && $params['group_type'] == 1) {// 走去拼团
                if (!isset($params['number']) || empty($params['number'])) {
                    return result(500, "缺少拼团人数");
                }
                return $this->groupOrder($params);
            } else {
                unset($params['group_type']); //拼团标识
                unset($params['number']); //拼团人数
                unset($params['group_id']); // 如果是参团有这个值
                unset($params['create_type']);
                if ($tuanconfig['status'] == 500) {
                    return $tuanconfig;
                } else if ($tuanconfig['status'] == 204 || $tuanconfig['data']['status'] == 0) {
                    $data = json_decode($params['goods'], true);

                    $id = "";
                    for ($i = 0; $i < count($data); $i++) {
                        if ($i == 0) {
                            $id = $data[$i]['goods_id'];
                        } else {
                            $id = $id . "," . $data[$i]['goods_id'];
                        }
                    }
                    $goodModel = new GoodsModel();
                    $goods = $goodModel->findall(["id in ({$id})" => null, 'delete_time' => 1]);
                    $len = count($goods['data']);
                    for ($i = 0; $i < $len; $i++) {
                        for ($j = $len - 1; $j > 0; $j--) {
                            if ($goods['data'][$i]['supplier_id'] != $goods['data'][$j]['supplier_id']) {
                                return result(500, "供应商商品与普通商品无法一起下单!");
                            }
                        }
                    }
                    $res = $this->shopOrder($params);
                    return $res;
                } else if ($tuanconfig['data']['status'] == 1) {
                    $time = date("Y-m-d", time());
                    if ($tuanconfig['data']['open_time'] + strtotime($time . " 00:00:00") <= time() && $tuanconfig['data']['close_time'] + strtotime($time . " 00:00:00") >= time()) {
                        return result(500, "团购未开市");
                    } else {
                        $data = json_decode($params['goods'], true);

                        $id = "";
                        for ($i = 0; $i < count($data); $i++) {
                            if ($i == 0) {
                                $id = $data[$i]['goods_id'];
                            } else {
                                $id = $id . "," . $data[$i]['goods_id'];
                            }
                        }
                        $goodModel = new GoodsModel();
                        $goods = $goodModel->findall(["id in ({$id})" => null, 'delete_time' => 1]);
                        $len = count($goods['data']);
                        for ($i = 0; $i < $len; $i++) {
                            for ($j = $len - 1; $j > 0; $j--) {
                                if ($goods['data'][$i]['supplier_id'] != $goods['data'][$j]['supplier_id']) {
                                    return result(500, "供应商商品与普通商品无法一起下单!");
                                }
                            }
                        }

                        $res = $this->tuanOrder($params);
                    }
                    return $res;
                } else {
                    return result(500, "系统错误");
                }
            }
        } else {
            return result(500, "请求方式错误");
        }
    }


    /**
     * 去付款
     * @param $id
     * @return array
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws yii\db\Exception
     */

    public function actionPay1($id)
    {
        if (yii::$app->request->isPost) {
            //微信内部调取支付接口
            $request = yii::$app->request; //获取 request 对象
            $params = $request->post(); //获取地址栏参数
            //订单金额
            $orderModel = new OrderModel();
            $order = $orderModel->find(['order_sn' => $id, '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id']]);
            $name = "";
            $money = 0.00;
            if ($order['status'] != 200) {
                $orders = $orderModel->findList(['transaction_order_sn' => $id, '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id']]);
                if ($orders['status'] != 200) {
                    return result(500, "订单信息无效！");
                }

                if ($orders['status'] == 200) {
                    $name = mb_substr($orders['data'][0]['goodsname'], 0, 10) . "...";

                    for ($i = 0; $i < count($orders['data']); $i++) {
                        $money = $money + $orders['data'][$i]['payment_money'];
                    }
                }
            } else {
                if ($order['status'] == 200) {
                    if (count($order['data']) > 1) {
                        $name = mb_substr($order['data']['goodsname'], 0, 10) . "...";
                    } else {
                        $name = mb_substr($order['data']['goodsname'], 0, 10) . "...";
                    }
                }
                $money = $money + $order['data']['payment_money'];
            }


            if ($params['type'] == 1) {
                $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogrampay", 1);
                if ($config == false) {
                    return result(500, "未配置微信信息");
                }
            } elseif ($params['type'] == 3) { //余额支付
                return self::balancePay($id);
            } else {
                $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogrampay", 1);
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
            $userModel->update(['id' => yii::$app->session['user_id'], '`key`' => yii::$app->session['key'], 'money' => $userData['data']['money'] + $money]);
            $orderModel->update1(['order_sn' => $id, '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id'], 'order_type' => $params['type'], 'transaction_order_sn' => $id]);
            file_put_contents(Yii::getAlias('@webroot/') . '/pay_order.text', date('Y-m-d H:i:s') . $config['wx_pay_type'] . PHP_EOL, FILE_APPEND);

            if ($config['wx_pay_type'] == 1) { // 微信支付

                $payment = Factory::payment($config);
                $wxPayData = array(
                    'body' => $name,
                    'attach' => 'shop',
                    'out_trade_no' => $id,
                    'total_fee' => $money * 100,
                    //'total_fee' => $order['data']['payment_money'],
                    'notify_url' => "https://".$_SERVER['SERVER_NAME']."/api/web/index.php/pay/wechat/notify1",
                    'trade_type' => 'JSAPI',
                );
                if ($params['type'] == 1) {
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

                    return result(500, $rs['return_msg']);
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
                $mini_pay->setTotal_fee($money * 100);
                $mini_pay->setOpen_id($userData['data']['mini_open_id']);
                $mini_pay->setNotify_url("https://".$_SERVER['SERVER_NAME']."/api/web/index.php/pay/wechat/notify-sao-bei");
                $pay_pre = Payx::miniPayRe($mini_pay, $config['saobei_access_token']);
                file_put_contents(Yii::getAlias('@webroot/') . '/pay_order_text1.xml', date('Y-m-d H:i:s') . json_encode($pay_pre) . PHP_EOL, FILE_APPEND);
                if ($pay_pre->return_code == "01" && $pay_pre->result_code == '01') {
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
                    return result(500, $pay_pre->return_msg);
                }
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionPay($id)
    {
        if (yii::$app->request->isPost) {
            //微信内部调取支付接口
            $request = yii::$app->request; //获取 request 对象
            $params = $request->post(); //获取地址栏参数
            //订单金额
            $orderModel = new OrderModel();
            $order = $orderModel->find(['order_sn' => $id, '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id']]);
            if ($order['status'] != 200) {
                return result(500, "订单信息无效！");
            }
            if ($params['type'] == 1) {
                $config = $this->getSystemConfig(yii::$app->session['key'], "wxpay", 1);
                if ($config == false) {
                    return result(500, "未配置微信信息");
                }
            } elseif ($params['type'] == 3) { //余额支付
                return self::balancePay($id);
            } else {
                $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogrampay", 1);
                if ($config == false) {
                    return result(500, "未配置小程序信息");
                }
            }
            if (count($order['data']) > 1) {
                $name = mb_substr($order['data']['goodsname'], 0, 10) . "...";
            } else {
                $name = mb_substr($order['data']['goodsname'], 0, 10) . "...";
            }
            //获取下单用户opid
            $userModel = new UserModel;
            $userData = $userModel->find(['id' => yii::$app->session['user_id']]);
            if ($userData['status'] != 200) {
                return result('500', '下单失败，找不到用户信息');
            }
            $userModel->update(['id' => yii::$app->session['user_id'], '`key`' => yii::$app->session['key'], 'money' => $userData['data']['money'] + $order['data']['payment_money']]);
            $orderModel->update(['order_sn' => $id, '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id'], 'order_type' => $params['type']]);
            file_put_contents(Yii::getAlias('@webroot/') . '/pay_order.text', date('Y-m-d H:i:s') . $config['wx_pay_type'] . PHP_EOL, FILE_APPEND);

            if ($config['wx_pay_type'] == 1) { // 微信支付

                $payment = Factory::payment($config);
                $wxPayData = array(
                    'body' => $name,
                    'attach' => 'shop',
                    'out_trade_no' => $id,
                    'total_fee' => $order['data']['payment_money'] * 100,
                    //'total_fee' => $order['data']['payment_money'],
                    'notify_url' => "https://api.juanpao.com/pay/wechat/notify",
                    'trade_type' => 'JSAPI',
                );
                if ($params['type'] == 1) {
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
                    return result(500, $rs['return_msg']);
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
                $mini_pay->setTotal_fee($order['data']['payment_money'] * 100);
                $mini_pay->setOpen_id($userData['data']['mini_open_id']);
                $mini_pay->setNotify_url("https://".$_SERVER['SERVER_NAME']."/api/web/index.php/pay/wechat/notify-sao-bei");
                $pay_pre = Payx::miniPayRe($mini_pay, $config['saobei_access_token']);
                file_put_contents(Yii::getAlias('@webroot/') . '/pay_order_text1.xml', date('Y-m-d H:i:s') . json_encode($pay_pre) . PHP_EOL, FILE_APPEND);
                if ($pay_pre->return_code == "01" && $pay_pre->result_code == '01') {
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
                    return result(500, $pay_pre->return_msg);
                }
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 余额支付
     * @param $order_sn
     * @return array
     * @throws yii\db\Exception
     */
    private function balancePay($order_sn)
    {
        //处理业务 先记录条日志
        $userModel = new UserModel;
        $userData = $userModel->find(['id' => yii::$app->session['user_id']]);
        if ($userData['status'] != 200) {
            return result('500', '支付失败，找不到用户信息');
        }
        $orderModel = new OrderModel;
        $orderRs = $orderModel->find(['transaction_order_sn' => $order_sn]);

        //检测余额是否足够支付


        //订单金额

        $order = $orderModel->find(['order_sn' => $order_sn, '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id']]);
        $money = 0.00;
        if ($order['status'] != 200) {
            $orders = $orderModel->findList(['transaction_order_sn' => $order_sn, '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id']]);
            if ($orders['status'] != 200) {
                return result(500, "订单信息无效！");
            }
            if ($orders['status'] == 200) {
                for ($i = 0; $i < count($orders['data']); $i++) {
                    $money = $money + $orders['data'][$i]['payment_money'];
                }
            }
        } else {
            $money = $money + $order['data']['payment_money'];
        }

        $recharge_balance = bcsub($userData['data']['recharge_balance'], $money, 2); //剩余余额
        if ($recharge_balance < 0) {
            return result('500', '余额不足请充值');
        }
        //检测订单是否是拼团订单
        $groupAccModel = new ShopAssembleAccessModel();
        $groupWhere['key'] = yii::$app->session['key'];
        $groupWhere['order_sn'] = $order_sn;
        $groupInfo = $groupAccModel->one($groupWhere);
        $status = 1;
        if ($groupInfo['status'] == 200) {
            $status = 11;
        } else {
            if ($orderRs['data']['service_goods_status'] == 1) {
                $status = 3;
            }
        }
        $orderData = array(
            'transaction_order_sn' => $order_sn,
            'status' => $status,
            'order_type' => 3,
        );

        //易联云自动推送，将订单号、key放入redis队列
        $ylyData['key'] = yii::$app->session['key'];
        $ylyData['order_sn'] = $order_sn;
        \Yii::$app->redis->lpush('ylyprint', json_encode($ylyData));

        //将订单号放入redis队列，用计划任务计算分销分佣金额
        $dtbData['order_sn'] = $order_sn;
        \Yii::$app->redis->lpush('distribution',json_encode($dtbData));

        try {
            $tr = Yii::$app->db->beginTransaction();
            $res = $orderModel->update($orderData);

            $orders = $orderModel->findList(['transaction_order_sn' => $order_sn, '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'user_id' => yii::$app->session['user_id']]);

            for ($i = 0; $i < count($orders['data']); $i++) {
                $order_sn = $orders['data'][$i]['order_sn'];

                if ($res['status'] != 200) {
                    $tr->rollBack();
                    return result(500, '支付失败');
                }
                //根据订单信息 减去总库存 和 各个商品库存
                $subOrderModel = new SubOrderModel();
                $subOrders = $subOrderModel->findall(['order_group_sn' => $order_sn]);
                for ($i = 0; $i < count($subOrders['data']); $i++) {
                    $stockModel = new StockModel();
                    $number = (int)$subOrders['data'][$i]['number'];
                    $stockdata["number = number-{$number}"] = NULL;
                    $stockdata['id'] = $subOrders['data'][$i]['stock_id'];
                    $res = $stockModel->update($stockdata);
                    if ($res['status'] != 200) {
                        $tr->rollBack();
                        return result(500, '支付失败');
                    }
                    $goodModel = new GoodsModel();
                    $gooddata["stocks= stocks-{$subOrders['data'][$i]['number']}"] = null;
                    $gooddata['id'] = $subOrders['data'][$i]['goods_id'];
                    $res = $goodModel->update($gooddata);
                    if ($res['status'] != 200) {
                        $tr->rollBack();
                        return result(500, '支付失败');
                    }
                }
                $payModel = new PayModel;
                $paydata = array(
                    'transaction_id' => $order_sn,
                    'order_id' => $order_sn,
                    'remain_price' => $orderRs['data']['payment_money'],
                    'total_price' => $orderRs['data']['total_price'],
                    'pay_time' => time(),
                    'status' => 1,
                    'type' => 1,
                    'merchant_id' => $orderRs['data']['merchant_id'],
                    'user_id' => $orderRs['data']['user_id'],
                    'update_time' => time(),
                );
                $res = $payModel->update($paydata);
                if ($res['status'] != 200) {
                    $tr->rollBack();
                    return result(500, '支付失败');
                }
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
                        $res = $tuanUserModel->do_add($tuanData);
                        if ($res['status'] != 200) {
                            $tr->rollBack();
                            return result(500, '支付失败');
                        }
                        $tuanUser = $tuanUserModel->do_one(['merchant_id' => $orderRs['data']['merchant_id'], 'uid' => $orderRs['data']['user_id']]);
                    }
                    if ($orderRs['data']['express_type'] == 2) {
                        $leaderModel = new \app\models\tuan\LeaderModel();
                        $leader = $leaderModel->do_one(['uid' => $orderRs['data']['leader_self_uid']]);
                        $balanceModel = new \app\models\shop\BalanceModel;
                        $data_ba_['money'] = $leader['data']['tuan_express_fee'];
                        $data_ba_['type'] = 6;
                        $data_ba_['uid'] = $orderRs['data']['leader_self_uid'];
                        $data_ba_['content'] = "配送费佣金";
                        $res = $balanceModel->do_add($data_ba_);
                        if ($res['status'] != 200) {
                            $tr->rollBack();
                            return result(500, '支付失败');
                        }
                    }
                    $balanceModel = new \app\models\shop\BalanceModel;
                    $balance = $this->balance($order_sn, $con['data']['commission_leader_ratio'], $con['data']['commission_selfleader_ratio']);
                    $data_ba = array(
                        'uid' => $tuanUser['data']['leader_uid'],
                        'order_sn' => $order_sn,
                        'money' => $balance[0],
                        'content' => "团员消费",
                        'type' => 1,
                        'status' => 0
                    );
                    $data_ba['key'] = $orderRs['data']['key'];
                    $data_ba['merchant_id'] = $orderRs['data']['merchant_id'];
                    $res = $balanceModel->do_add($data_ba);
                    if ($res['status'] != 200) {
                        $tr->rollBack();
                        return result(500, '支付失败');
                    }
                    $balanceModel = new \app\models\shop\BalanceModel;
                    if ($orderRs['data']['leader_self_uid'] != 0) {
                        $data_ba['money'] = $balance[1];
                        $data_ba['type'] = 3;
                        $data_ba['uid'] = $orderRs['data']['leader_self_uid'];
                        $data_ba['content'] = "自提点佣金";
                        $res = $balanceModel->do_add($data_ba);
                        if ($res['status'] != 200) {
                            $tr->rollBack();
                            return result(500, '支付失败');
                        }
                    }
                }
                $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
                $comboAccessData = $comboAccessModel->do_one(['<>' => ['order_remain_number', 0], '>' => ['validity_time', time()], 'merchant_id' => $orderRs['data']['merchant_id']]);
                $res = $comboAccessModel->do_update(['id' => $comboAccessData['data']['id']], ['order_remain_number' => $comboAccessData['data']['order_remain_number'] - 1]);
                if ($res['status'] != 200) {
                    $tr->rollBack();
                    return result(500, '支付失败');
                }
                //扣除用户余额
                $res = $userModel->update(['id' => $orderRs['data']['user_id'], '`key`' => $orderRs['data']['key'], 'recharge_balance' => $recharge_balance]);
                if ($res['status'] != 200) {
                    $tr->rollBack();
                    return result(500, '支付失败');
                }
                //增加消费记录balance
                $data_balance = array(
                    'uid' => $orderRs['data']['user_id'],
                    'order_sn' => $orderRs['data']['order_sn'],
                    'money' => '-' . $orderRs['data']['payment_money'],
                    'remain_money' => 0.00,
                    'content' => "余额下单",
                    'type' => 8,
                    'send_type' => 0,
                    'is_recharge_balance' => 0,
                    'status' => 1
                );
                $data_balance['key'] = $orderRs['data']['key'];
                $data_balance['merchant_id'] = $orderRs['data']['merchant_id'];
                $balanceModel = new \app\models\shop\BalanceModel;
                $res = $balanceModel->do_add($data_balance);
            }
            if ($res['status'] == 200) {
                $tr->commit();
                return result(200, '支付成功');
            }
            $tr->rollBack();
            return result(500, '支付失败');
        } catch (\Exception $e) {
            return result(500, $e->getMessage());
        }
    }

    /**
     * 佣金计算
     * @param $order_sn
     * @param $commission_leader_ratio
     * @param $commission_selfleader_ratio
     * @return mixed
     * @throws yii\db\Exception
     */
    private function balance($order_sn, $commission_leader_ratio, $commission_selfleader_ratio)
    {
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
     * 取消退款申请
     */
    public function actionUnmoney($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $config = $this->getSystemConfig(yii::$app->session['key'], "wxpay");
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $model = new OrderModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $data['order_sn'] = $id;
            $order = $model->find($data);
            if ($order['status'] != 200) {
                return result('204', "找不到该订单");
            }
            if ($order['after_sale'] != 0) {
                return result('204', "找不到该订单或者该订单已通过申请");
            }
            $data['after_sale'] = -1;
            $res = $model->update($data);
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 取消订单 取消微信订单
     */
    public function actionUnorder($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数

            $model = new OrderModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $data['order_sn'] = $id;
            $order = $model->find($data);
            //var_dump(order['data']['order_type']);die();
//            if ($order['data']['order_type'] == 1) {
//                $config = $this->getSystemConfig(yii::$app->session['key'], "wxpay", 1);
//            }
            //  if ($order['data']['order_type'] == 2) {
            $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogrampay", 1);
            //  }
            ///  $this->logger(json_encode($config));die();
//            return result(500, $config);
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            if (!isset($config['wx_pay_type'])) {
                if ($order['status'] != 200) {
                    return result('204', "找不到该订单");
                }
                $data['status'] = 2;
                $res = $model->update($data);
                //恢复优惠券状态
                if ($order['data']['voucher_id'] != 0) {
                    $voucher = new VoucherModel;
                    $vData['id'] = $order['data']['voucher_id'];
                    $vData['is_used'] = 0;
                    $voucher->update($vData);
                }
                if ($res['status'] == 200) {
                    //这里应该执行微信关闭订单方法，原撤销订单代码先注释
//                $payment = Factory::payment($config);
//                $payment->reverse->byOutTradeNumber($id);
                    //订单关闭方法同样的错误
                    $payment = Factory::payment($config);
                    $payment->order->close($id);
                }
                return $res;

            } else {
                if ($config['wx_pay_type'] == 1) {
                    if ($order['status'] != 200) {
                        return result('204', "找不到该订单");
                    }
                    $data['status'] = 2;
                    $res = $model->update($data);
                    //恢复优惠券状态
                    if ($order['data']['voucher_id'] != 0) {
                        $voucher = new VoucherModel;
                        $vData['id'] = $order['data']['voucher_id'];
                        $vData['is_used'] = 0;
                        $voucher->update($vData);
                    }
                    if ($res['status'] == 200) {
                        //这里应该执行微信关闭订单方法，原撤销订单代码先注释
//                $payment = Factory::payment($config);
//                $payment->reverse->byOutTradeNumber($id);
                        //订单关闭方法同样的错误
                        $payment = Factory::payment($config);
                        $payment->order->close($id);
                    }
                    return $res;
                }
                if ($config['wx_pay_type'] == 2) {
                    if ($order['status'] != 200) {
                        return result('204', "找不到该订单");
                    }
                    $data['status'] = 2;
                    $res = $model->update($data);
                    //恢复优惠券状态
                    if ($order['data']['voucher_id'] != 0) {
                        $voucher = new VoucherModel;
                        $vData['id'] = $order['data']['voucher_id'];
                        $vData['is_used'] = 0;
                        $voucher->update($vData);
                    }
                    if ($res['status'] == 200) {
                        //扫呗取消订单
                    }
                    return $res;
                }
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new OrderModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 确认收货
     */
    public function actionGoods()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new OrderModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            if (!isset($params['order_sn'])) {
                return result(400, "缺少参数 订单号");
            } else {
                $data['order_sn'] = $params['order_sn'];
                $data['status'] = 6;
                $array = $model->update($data);
                if ($array['status'] != 200) {
                    return $array;
                }
                $subOrder = new SubOrderModel();
                $sub['`key`'] = yii::$app->session['key'];
                $sub['merchant_id'] = yii::$app->session['merchant_id'];
                $sub['user_id'] = yii::$app->session['user_id'];
                $sub['order_group_sn'] = $params['order_sn'];
                $sub['confirm_time'] = time();
                $subOrder->update($sub);

                //vip权益
                $sql = "select is_vip,vip_validity_time from shop_user where id = " . yii::$app->session['user_id'];
                $vipUser = $subOrder->querySql($sql);
                $vip = 1;
                if ($vipUser[0]['is_vip'] == 1 && $vipUser[0]['vip_validity_time'] > time()) {
                    $sql = "select score_times from shop_vip_config where merchant_id = " . yii::$app->session['merchant_id'] . " `key` = '" . yii::$app->session['key'] . "'";
                    $vipConfig = $subOrder->querySql($sql);
                    if (count($vipConfig) != 0) {
                        $vip = $vipConfig[0]['score_times'];
                    }
                }
                $rs = $model->tableSingle("shop_order_group", ['order_sn' => $params['order_sn'], 'delete_time is null' => null]);
                $scoreModel = new ScoreModel();

                $scoreData = array(
                    '`key`' => yii::$app->session['key'],
                    'merchant_id' => yii::$app->session['merchant_id'],
                    'user_id' => yii::$app->session['user_id'],
                    'score' => $rs['payment_money'] * $vip,
                    'content' => '购买商品送积分',
                    'type' => '1',
                    'status' => '1'
                );
                $scoreModel->add($scoreData);

                $configModel = new \app\models\tuan\ConfigModel();

                $config = $configModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);
                if ($config['status'] == 200 && $config['data']['status'] == 1) {
                    //团长佣金
                    $balanceModel = new \app\models\shop\BalanceModel();
                    $balance = $balanceModel->do_one(['order_sn' => $params['order_sn'], 'type' => 1, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
                    if ($balance['status'] == 200) {
                        $userModel = new UserModel();
                        $user = $userModel->find(['id' => $balance['data']['uid']]);
                        if ($user['status'] == 200) {
                            $userModel->update(['id' => $balance['data']['uid'], '`key`' => yii::$app->session['key'], 'balance' => (float)$user['data']['balance'] + (float)$balance['data']['money']]);
                        }
                    }
                }
                //供应商金额
                $subBalanceModel = new \app\models\system\SystemSubAdminBalanceModel();
                $subBalance = $subBalanceModel->do_select(['order_sn' => $params['order_sn']]);
                if ($subBalance['status'] == 200) {
                    $subBalanceModel->do_update(['order_sn' => $params['order_sn']], ['status' => 1]);
                    for ($i = 0; $i < count($subBalance['data']); $i++) {
                        $subUserModel = new \app\models\merchant\system\UserModel();
                        $sql = "update system_sub_admin set balance = balance+{$subBalance['data'][$i]['money']} where id = {$subBalance['data'][$i]['sub_admin_id']}";
                        $subUserModel->querySql($sql);
                    }
                }

                $subOrder = new SubOrderModel();
                $sub['`key`'] = yii::$app->session['key'];
                $sub['merchant_id'] = yii::$app->session['merchant_id'];
                $sub['user_id'] = yii::$app->session['user_id'];
                $sub['order_group_sn'] = $params['order_sn'];
                $suborders = $subOrder->findall($sub);

                if($suborders['status']==200){
                    for($i=0;$i<count($suborders['data']);$i++){
                        $goods_ids[$i] = $suborders['data'][$i]['goods_id'];
                    }
                    $cashBackModel = new CashbackModel();
                    $res = $cashBackModel->do_select(['goods_id'=>$goods_ids]);

                    if($res['status']==200){
                        $price = 0;
                        for($j=0;$j<count($res['data']);$j++){
                            for($k=0;$k<count($suborders['data']);$k++){
                                if($res['data'][$j]['goods_id']==$suborders['data'][$k]['goods_id']){

                                    $price = $price+$suborders['data'][$k]['payment_money'];
                                }
                            }
                        }

                        $sql = "update shop_user set recharge_balance=recharge_balance+{$price} where id =".yii::$app->session['user_id'].";";

                        Yii::$app->db->createCommand($sql)->execute();
                    }
                }
                $orderModel = new OrderModel;
                $orderRs = $orderModel->find(['order_sn' => $params['order_sn']]);

                $shopUserModel = new \app\models\shop\UserModel();
                $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);

                $tempModel = new \app\models\system\SystemMiniTemplateModel();
                $minitemp = $tempModel->do_one(['id' => 32]);
                //单号,金额,下单时间,物品名称,
                // [{"keyword_id":"1","name":"订单号","example":"201703158237869"},
                //{"keyword_id":"3","name":"完成时间","example":"2017-03-22 10:04:12"},
                //{"keyword_id":"5","name":"订单号码","example":"201703158237869"},
                //{"keyword_id":"12","name":"联系电话","example":"13899990000"},
                $tempParams = array(
                    'keyword1' => $params['order_sn'],
                    'keyword2' => $orderRs['data']['update_time'],
                    'keyword3' => $orderRs['data']['create_time'],
                    'keyword4' => $orderRs['data']['phone'],
                );

                $tempAccess = new SystemMerchantMiniAccessModel();
                $taData = array(
                    'key' => $orderRs['data']['key'],
                    'merchant_id' => $orderRs['data']['merchant_id'],
                    'mini_open_id' => $shopUser['data']['mini_open_id'],
                    'template_id' => 32,
                    'number' => '0',
                    'template_params' => json_encode($tempParams),
                    'template_purpose' => 'order',
                    'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn']}",
                    'status' => '-1',
                );
                $tempAccess->do_add($taData);

                //用户确认收货后，查询普通会员是否可以升级为超级会员
                $appAccessModel = new \app\models\merchant\app\AppAccessModel();
                $appInfo = $appAccessModel->find(['key'=>$orderRs['data']['key']]);
                $userModel = new UserModel;
                $userInfo = $userModel->find(['id' => $orderRs['data']['user_id']]);
                //会员等级为普通会员的再做后续判断
                if ($userInfo['status'] == 200 && $userInfo['data']['level'] == 0){
                    $superModel = new SuperModel();
                    $superInfo = $superModel->one(['key'=>$orderRs['data']['key']]);
                    //未查询到超级会员设置信息的，不做处理
                    if ($superInfo['status'] == 200){
                        //用户消费金额达到设定则升级，否则不做处理
                        $groupOrderModel = new GroupOrderModel();
                        $groupOrderWhere['field'] =  "sum(payment_money) as money";
                        $groupOrderWhere['user_id'] = $orderRs['data']['user_id'];
                        $groupOrderWhere['or'] = ['or',['=','status', 6],['=','status', 7]];
                        $moneyInfo = $groupOrderModel->one($groupOrderWhere);
                        if ($moneyInfo['status'] == 200 && $moneyInfo['data']['money'] >= $superInfo['data']['condition']){
                            //是否开启手动审核
                            if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0){
                                $levelData['id'] = $orderRs['data']['user_id'];
                                $levelData['`key`'] = $orderRs['data']['key'];
                                $levelData['level'] = 1;
                                $levelData['up_level'] = 1;
                                $userModel->update($levelData);
                            }else{
                                $levelData['id'] = $orderRs['data']['user_id'];
                                $levelData['`key`'] = $orderRs['data']['key'];
                                $levelData['up_level'] = 1;
                                $levelData['is_check'] = 0;
                                $userModel->update($levelData);
                            }
                        }
                    }
                }
                //判断父级是否可以升级
                if ($userInfo['status'] == 200 && !empty($userInfo['data']['parent_id'])){
                    $parentInfo = $userModel->find(['id'=>$userInfo['data']['parent_id']]);
                    $sql = "SELECT sum(sog.payment_money) as total FROM `shop_user` su RIGHT JOIN `shop_order_group` sog ON sog.user_id = su.id WHERE su.parent_id = {$userInfo['data']['parent_id']} AND sog.status = 6 OR sog.status = 7";
                    $total = $userModel->querySql($sql); //$total[0]['total']
                    if (isset($parentInfo['data'])){
                        $fanNum = $parentInfo['data']['fan_number'];
                        $secondhandFanNum = $parentInfo['data']['secondhand_fan_number'];
                        $level = $parentInfo['data']['level'];
                        $agentModel = new AgentModel();
                        $agentWhere['key'] = $orderRs['data']['key'];
                        $agentWhere['merchant_id'] = $orderRs['data']['merchant_id'];
                        $agentWhere['status'] = 1;
                        $agentWhere['limit'] = false;
                        $agentInfo = $agentModel->do_select($agentWhere);
                        if (isset($agentInfo['data'])){
                            foreach ($agentInfo['data'] as $k=>$v){
                                if ($v['fan_number_buy'] <= $total[0]['total'] && $v['fan_number'] <= $fanNum && $v['secondhand_fan_number'] <= $secondhandFanNum){
                                    $level = 2;
                                    $levelId = $v['id'];
                                }
                            }
                        }
                        $operatorModel = new OperatorModel();
                        $operatorWhere['key'] = $orderRs['data']['key'];
                        $operatorWhere['merchant_id'] = $orderRs['data']['merchant_id'];
                        $operatorWhere['status'] = 1;
                        $operatorWhere['limit'] = false;
                        $operatorInfo = $operatorModel->do_select($operatorWhere);
                        if (isset($operatorInfo['data'])){
                            foreach ($operatorInfo['data'] as $k=>$v){
                                if ($v['fan_number_buy'] <= $total[0]['total'] && $v['fan_number'] <= $fanNum && $v['secondhand_fan_number'] <= $secondhandFanNum){
                                    $level = 3;
                                    $levelId = $v['id'];
                                }
                            }
                        }
                        //是否开启手动审核
                        if ($level > $parentInfo['data']['level'] || ($level == $parentInfo['data']['level'] && $levelId != $parentInfo['data']['level_id'])){
                            $levelData['id'] = $userInfo['data']['parent_id'];
                            $levelData['`key`'] = $orderRs['data']['key'];
                            $levelData['up_level'] = $level;
                            if (isset($levelId)){
                                $levelData['up_level_id'] = $levelId;
                            }
                            if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0){
                                $levelData['level'] = $level;
                                if (isset($levelId)){
                                    $levelData['level_id'] = $levelId;
                                }
                            }else{
                                $levelData['is_check'] = 0;
                            }
                            $userModel->update($levelData);
                        }
                    }
                }

                //确认收货后,将每个人的预估分销佣金计入可提现分销佣金中,将订单表中未分配完的佣金计入应用表未分配佣金池
                $userModel = new UserModel();
                $distributionAccessModel = new DistributionAccessModel();
                $accessWhere['key'] = yii::$app->session['key'];
                $accessWhere['merchant_id'] = yii::$app->session['merchant_id'];
                $accessWhere['order_sn'] = $params['order_sn'];
                $accessWhere['type'] = 1; //订单提佣
                $accessWhere['limit'] = false;
                $distributionAccess = $distributionAccessModel->do_select($accessWhere);
                if ($distributionAccess['status'] == 200){
                    foreach ($distributionAccess['data'] as $k=>$v){
                        $userInfo = $userModel->find(['id'=>$v['uid']]);
                        if ($userInfo['status'] == 200){
                            $userData['id'] = $v['uid'];
                            $userData['`key`'] = $v['key'];
                            $userData['withdrawable_commission'] = $v['money'] + $userInfo['data']['withdrawable_commission'];
                            $userModel->update($userData);
                        }
                    }
                }
                $appData = [];
                $appData['`key`'] = $userInfo['data']['key'];
                $appData['merchant_id'] = $userInfo['data']['merchant_id'];
                $appData['commissions_pool'] = $orderRs['data']['commissions_pool'] + $appInfo['data']['commissions_pool'];
                $appAccessModel->update($appData);

                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new OrderModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $data['order_sn'] = $id;
            $data['status'] = 8;
            $array = $model->update($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAfter()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new OrderModel();
            $params['`key`'] = yii::$app->session['key'];

            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $type = $params['type'];
            unset($params['type']);
            if (!isset($params['order_sn'])) {
                return result(500, "缺少参数 order_sn");
            } else {
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['user_id'] = yii::$app->session['user_id'];
                $data['order_sn'] = $params['order_sn'];
                $order = $model->find($data);
                //状态 0=待付款 1=待发货 2=已取消(24小时未支付) 3=已发货 4=已退款 5=退款中 6=待评价 7=已完成(评价后)  8=已删除
                if ($order['status'] == 200) {
                    if ($order['data']['after_sale'] == 1) {
                        if ($order['data']['status'] == 5) {//退款退货  同意  -填写退货单号，地址，电话
                            $data['after_express_number'] = $params['after_express_number'];
                            $array = $model->update($data);
                            return $array;
                        } else {
                            return result(204, "卖家未同意退款退货！");
                        }
                    } else {
                        if ($order['data']['status'] == 1) {//仅退款
                            if ($params['after_type'] != 2) {
                                return result(500, "您的订单未发货，只能选择仅退款！");
                            }
                            $data['after_type'] = 2;
                            $data['after_sale'] = 0;
                            $data['status'] = 5;
                        } elseif ($order['data']['status'] == 3) {//退款退货
                            if ($params['after_type'] != 1) {
                                return result(500, "您的订单已发货，只能选择退款退货！");
                            }
                            $data['after_type'] = 1;
                            $data['after_sale'] = 0;
                            $data['status'] = 5;
                        } else {
                            return result(500, "该订单不能申请退款退货！");
                        }
                        if (isset($params['after_imgs'])) {
                            if ($type == 1) {
                                $config = $this->getSystemConfig(yii::$app->session['key'], "wechat");
                                if ($config == false) {
                                    return result(500, "未配置微信信息");
                                }
                                $data['after_imgs'] = $this->wxUpload($config, $params['after_imgs']);
                            } else {
                                //$data['after_imgs'] = $this->xcxUploads($params['after_imgs']);
                            }
                        }
                        $data['after_remark'] = $params['after_remark'];
                        $array = $model->update($data);
                        return $array;
                    }
//                    if ($order['data']['after_sale'] == -1) {
//
//                    } else if ($order['data']['after_sale'] == 2) {
//                        if ($order['data']['status'] == 5) {//退款退货  同意  -填写退货单号，地址，电话
//                            $data['after_express_number'] = $params['after_express_number'];
//                            $array = $model->update($data);
//                            return $array;
//                        } else {
//                            return result(204, "卖家未同意退款退货！");
//                        }
//                    }
                } else {
                    return result(204, "找不到该订单！");
                }
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAfterlist()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new OrderModel();
            $params['shop_order_group.`key`'] = yii::$app->session['key'];
            $params['shop_order_group.merchant_id'] = yii::$app->session['merchant_id'];
            $params['shop_order_group.user_id'] = yii::$app->session['user_id'];

            if ($params['status'] == 1) {
                $params['shop_order_group.status = 5 and shop_order_group.after_sale = 0'] = null;
            } else if ($params['status'] == 2) {
                $params['shop_order_group.status = 5 and shop_order_group.after_sale = 1 and shop_order_group.after_type = 1 '] = null;
            } else if ($params['status'] == 3) {
                $params['shop_order_group.status = 4 '] = null;
            } else {
                $params[' (shop_order_group.status = 5 or (shop_order_group.status = 7 and shop_order_group.after_sale = 1) or shop_order_group.status = 4 )'] = null;
            }
            unset($params['status']);
            $array = $model->shop_order($params);
            if ($array['status'] != 200) {
                return $array;
            }
            foreach ($array['data'] as $key => $value) {
                if ($value['after_type'] == 1) {//退款退货
                    if ($value['after_addr'] == null) {
                        $array['data'][$key]['after_status'] = 1; //待退款 -退款退货
                    } else if ($value['after_sale'] == 2) {
                        $array['data'][$key]['after_status'] = 2; //商户拒绝 -退款退货
                    } else {
                        if ($value['after_express_number'] == null) {
                            $array['data'][$key]['after_status'] = 6; //商家同意退款退货 -等待用户填写运单号
                        } else {
                            $array['data'][$key]['after_status'] = 7; //商家同意退款退货 -等待商户确认
                        }
                    }
                } else if ($value['after_type'] == 2) {//只退款
                    if ($value['after_sale'] == 1) {
                        $array['data'][$key]['after_status'] = 3; //商家同意退款 -只退款
                    } else if ($value['after_sale'] == 2) {
                        $array['data'][$key]['after_status'] = 4; //商家拒绝退款 -只退款
                    } else {
                        $array['data'][$key]['after_status'] = 5; //待退款 -只退款
                    }
                } else {
                    $array['data'][$key]['after_status'] = 0; //未售后
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUploads()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            //设置类目 参数
            $upload = new UploadsModel('pic_url', "./uploads/goods");
            $str = $upload->upload();
            if (!$str) {
                return "上传文件错误";
            }
            //将图片上传到cos
            $cos = new CosModel();
            $cosModel = new SystemCosModel();
            $a =  $cosModel->do_select([]);
            if($a['status']==200){
                $cosRes = $cos->putObject($str);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $str);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $str);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
            }else{
                $str = "http://".$_SERVER['HTTP_HOST']."/api/web/".$str;
                $url  =  $str;
            }
            return $url;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function Kdf($id, $number)
    {

        $model = new ContactModel();
        $params['id'] = $id;
        $params['`key`'] = yii::$app->session['key'];
        // $params['merchant_id'] = yii::$app->session['merchant_id'];
        $params['user_id'] = yii::$app->session['user_id'];

        $tempModel = new ShopExpressTemplateModel();
        $data['merchant_id'] = yii::$app->session['merchant_id'];
        $data['`key`'] = yii::$app->session['key'];
        $data['status'] = 1;
        $temp = $tempModel->find($data);
        if ($temp['status'] != 200) {
            return result(500, "快递费获取失败");
        }
        $address = $model->find($params);

        $price = 0;
        $kdmb = new ShopExpressTemplateDetailsModel();

        unset($params['id']);
        $data['searchName'] = $address['data']['province'];
        $data['merchant_id'] = yii::$app->session['merchant_id'];
        $data['`key`'] = yii::$app->session['key'];
        $data['shop_express_template_id'] = $temp['data']['id'];
        $data['status'] = 1;

        if ($address['status'] == 200) {
            $data['searchName'] = $address['data']['province'];
            $kdf = $kdmb->find($data);
        } else {
            $params['searchName'] = "全国统一运费";
            $kdf = $kdmb->find($data);
        }
        if ($kdf['status'] != 200) {
            $data['searchName'] = "全国统一运费";
            $kdf = $kdmb->find($data);
            $price = $kdf['data']['expand_price'];
        }

        $price = $kdf['data']['first_price'] + (($number - 1) * $kdf['data']['expand_price']);
        $price = $price == 0 ? "0" : $price;
        return $price;
    }

    public function actionQrcode()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $qrcode = getRedis(json_encode($params));

            if ($qrcode) {
                return result(200, "请求成功", $qrcode);
            } else {
                error_reporting(E_ERROR);
                require_once yii::getAlias('@vendor/wxpay/example/qrcode.php');
                creat_mulu1('uploads/qrcode');
                $qrcode = "./uploads/qrcode/" . time() . rand(1000, 9999) . ".png";
                \QRcode::png(json_encode($params), $qrcode);
                //将图片上传到cos
                $cos = new CosModel();
                $cosRes = $cos->putObject($qrcode);
                if ($cosRes['status'] == 200) {
                    $qrcode = $cosRes['data'];
                }else{
                    $qrcode = "https://".$_SERVER['SERVER_NAME']."/api/web/".$qrcode;
                }
                setConfig(json_encode($params), $qrcode);
            }
            return result(200, "请求成功", $qrcode);
        } else {
            return result(500, "请求方式错误");
        }
    }

    //拼团商品创建订单
    public function groupOrder($params, $service_goods_status = 0)
    {
        $params['group_id'] = $params['group_id'] == '' ? 0 : $params['group_id'];
        //检测当前下单人是否参团过
        $groupModel = new ShopAssembleModel();
        $groupOrderModel = new ShopAssembleAccessModel();
        $weight = 0;
        $params['goods'] = json_decode($params['goods'], true);
        $params['`key`'] = yii::$app->session['key'];
        if ($params['group_id']) {
            $groupOrderInfos = $groupOrderModel->one(['leader_id' => $params['group_id'], 'uid' => yii::$app->session['user_id'], 'goods_id' => $params['goods'][0]['list'][0]['goods_id']]);
            if ($groupOrderInfos['status'] == 200) {
                return result(500, "不能再次参与拼团");
            }
            $leGroupOrderInfos = $groupOrderModel->one(['id' => $params['group_id'], 'uid' => yii::$app->session['user_id'], 'goods_id' => $params['goods'][0]['list'][0]['goods_id']]);
            if ($leGroupOrderInfos['status'] == 200) {
                return result(500, "不能参与拼团");
            }
            //检测当前拼团人数是否超出
            $totals = $groupOrderModel->get_count(['leader_id' => $params['group_id'], 'key' => $params['`key`']]);
            $totals = $totals + 1;
            $leaderOrderInfo_s = $groupOrderModel->one(['id' => $params['group_id']]);
            if ($leaderOrderInfo_s['status'] != 200) {
                return result(500, "此团不能使用了");
            }
            if ($leaderOrderInfo_s['data']['number'] <= $totals) {
                return result(500, "此团已满请重新选择");
            }
        }
        $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
        $comboAccessData = $comboAccessModel->do_one(['<>' => ['order_remain_number', 0], '>' => ['validity_time', time()], 'orderby' => 'id asc', 'merchant_id' => yii::$app->session['merchant_id']]);

        if ($comboAccessData['status'] != 200) {
            return result(500, "下单失败,商户信息错误");
        }
        if ($comboAccessData['data']['order_remain_number'] < 1) {
            return result(500, "商户订单数量不足，下单失败");
        }
        /**
         * 查询优惠券
         */
        $voucherModel = new VoucherModel();
        $voucherParams['user_id'] = yii::$app->session['user_id'];
        $voucherParams['merchant_id'] = yii::$app->session['merchant_id'];
        if (isset($params['voucher_id'])) {
            if ($params['voucher_id'] != "") {
                $voucherData['id'] = $params['voucher_id'];
                $voucherData = $voucherModel->find($voucherData);
                if ($voucherData['status'] != 200) {
                    return result(500, "该优惠券已使用，或已失效！");
                }
            } else {
                $voucherData = false;
            }
        } else {
            $voucherData = false;
        }

        /**
         * 计算商品总价格 商品名称拼接
         */
        $stockModel = new StockModel();
        $goodModel = new GoodsModel();
        $total_price = 0;
        $name = "";
        $subGoods = array();
        $number = 0;
        $orderGroupModel = new OrderModel();
        for ($i = 0; $i < count($params['goods'][0]['list']); $i++) {
            $stockData = $stockModel->find(['id' => $params['goods'][0]['list'][$i]['stock_id']]);
            $goodData = $goodModel->find(['id' => $params['goods'][0]['list'][$i]['goods_id']]);
            if ($goodData['status'] != 200 && $stockData['status'] != 200) {
                return result(500, "找不到该商品或商品已下架");
            }
            if (count($params['goods'][0]['list']) == 1 && $goodData['data']['type'] == 3 && $goodData['data']['service_goods_is_ship'] == 1) {
                $service_goods_status = 1;
            }
            if ($goodData['data']['is_limit'] == 1 && $goodData['data']['limit_number'] > 0) { // 检测此商品被购买了多少次
                $sql = "SELECT sum(so.number) as total FROM shop_order_group as sog
                          LEFT JOIN shop_order as so ON sog.order_sn = so.order_group_sn WHERE  so.goods_id = {$params['goods'][0]['list'][$i]['goods_id']} and sog.`status` in  (0,1,3,5,6,7) and sog.user_id = {$voucherParams['user_id']} ";
                $total = $orderGroupModel->querySql($sql);
                if ((int)$total[0]['total'] >= (int)$goodData['data']['limit_number']) {
                    return result(500, "此商品已限量了！");
                }
            }
            if ($stockData['data']['number'] == 0) {
                return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}已售罄!");
            } else if ($stockData['data']['number'] < $params['goods'][0]['list'][$i]['number']) {
                return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}购买数量超出库存!");
            }
            //查询商品拼团价格
            $groupWhere['goods_id'] = $params['goods'][0]['list'][$i]['goods_id'];
            $groupWhere['key'] = yii::$app->session['key'];
            $groupWhere['status'] = 1;
            $groupInfo = $groupModel->one($groupWhere);
            if ($groupInfo['status'] != 200) {
                return result(500, "拼团数据出错了！");
            }
            $wheredata['property1_name'] = $params['goods'][0]['list'][$i]['property1_name'];
            $wheredata['property2_name'] = $params['goods'][0]['list'][$i]['property2_name'];
            $wheredata['number'] = $params['number'];
            //判断是否开启老带薪
            if ($groupInfo['data']['older_with_newer']) {
                //查询当前发起拼团或者参加拼团的人是都否下过订单
                $sql = "SELECT id FROM shop_order_group
                        WHERE `key` = '{$groupWhere['key']}' and user_id = {$voucherParams['user_id']} and `status` in  (1,3,5,6,7)";
                $orderinfo = $orderGroupModel->querySql($sql);
                if ((int)$params['group_id']) {
                    if (empty($orderinfo)) {
                        return result(500, "当前商品只支持下过订单的老用户开团！");
                    }
                } else {
                    if (empty($orderinfo)) {
                        return result(500, "当前商品只支持下过订单的老用户开团！");
                    }
                }
            };
            $is_leader_discount = $params['group_id'] == 0 ? 1 : 0;
            $goods_price = $groupModel::searchGroupPrice($groupInfo['data']['property'], $wheredata, $is_leader_discount);
            if ($i == 0) {
                $total_price = $goods_price;
                $name = $goodData['data']['name'];
            } else {
                $total_price = $total_price + $goods_price;
                $name = $name . "," . $goodData['data']['name'];
            }
            $number = 1;

            //子订单数据
            $subGoods[$i]['goods_id'] = $goodData['data']['id'];
            $subGoods[$i]['stock_id'] = $stockData['data']['id'];
            $subGoods[$i]['pic_url'] = $stockData['data']['pic_url'];
            $weight = $stockData['data']['weight'];
            $subGoods[$i]['name'] = $goodData['data']['name'];
            $subGoods[$i]['number'] = $params['goods'][0]['list'][$i]['number'];
            $subGoods[$i]['price'] = $stockData['data']['price'];
            $subGoods[$i]['total_price'] = $goods_price;
            $subGoods[$i]['property1_name'] = isset($params['goods'][0]['list'][$i]['property1_name']) ? $params['goods'][0]['list'][$i]['property1_name'] : "";
            $subGoods[$i]['property2_name'] = isset($params['goods'][0]['list'][$i]['property2_name']) ? $params['goods'][0]['list'][$i]['property2_name'] : "";
        }
        if ($voucherData == FALSE) {
            $payment_money = $total_price;
        } else {
            if ($voucherData['data']['full_price'] == 0 || $voucherData['data']['full_price'] <= $total_price) {
                $payment_money = $total_price - $voucherData['data']['price'];
            } else {
                return result(500, "该优惠券未达到使用标准！");
            }
        }

        $voucher_id = $voucherData['data']['id'];
        //收货地址
        //     return result(500, $params['type']);
        if ($params['type'] == 1) {
            $express_price['data'] = 0;

            $contactData['data']['phone'] = $params['phone'];
            $contactData['data']['name'] = $params['name'];
            $user_contact_id = 0;
            $address = "";
        } else if ($params['type'] == 2) {
            if ($params['user_contact_id'] == 0 || $params['user_contact_id'] == "") {
                $express_price['data'] = 0;

                $contactData['data']['phone'] = $params['phone'];
                $contactData['data']['name'] = $params['name'];
                $user_contact_id = 0;
                $address = "";
            } else {
                $contactModel = new ContactModel();
                if (!isset($params['user_contact_id'])) {
                    return result(500, '请填写收货地址');
                }
                $contactParams['id'] = $params['user_contact_id'];
                $contactParams['user_id'] = yii::$app->session['user_id'];
                $contactData = $contactModel->find($contactParams);
                if ($contactData['status'] != 200) {
                    return result(500, '未找到该收货地址');
                }
                $user_contact_id = $contactData['data']['id'];
                //快递费
                $address = $contactData['data']['province'] . "-" . $contactData['data']['city'] . "-" . $contactData['data']['area'] . "-" . $contactData['data']['street'] . $contactData['data']['address'] . "-" . $contactData['data']['postcode'];
            }

            $tuanLeaderModel = new \app\models\tuan\LeaderModel();
            $lerder = $tuanLeaderModel->do_one(['uid' => $params['leader_id']]);

            if ($lerder['data']['is_tuan_express'] == 0) {
                return result(500, "该门店未开启配送");
            }
            $express_price['data'] = $lerder['data']['tuan_express_fee'];
        } else {
            $contactModel = new ContactModel();
            if (!isset($params['user_contact_id'])) {
                return result(500, '请填写收货地址');
            }
            $contactParams['id'] = $params['user_contact_id'];
            $contactParams['user_id'] = yii::$app->session['user_id'];
            $contactData = $contactModel->find($contactParams);
            if ($contactData['status'] != 200) {
                return result(500, '未找到该收货地址');
            }
            $user_contact_id = $contactData['data']['id'];
            //快递费
            $express_price = $this->express($number, $contactData['data']['id'], $weight);
            if ($express_price['status'] != 200) {
                return $express_price;
            }
            $address = $contactData['data']['province'] . "-" . $contactData['data']['city'] . "-" . $contactData['data']['area'] . "-" . $contactData['data']['street'] . $contactData['data']['address'] . "-" . $contactData['data']['postcode'];
        }


        //查询订单唯一
        do {
            $order_sn = order_sn();
            $orderFindData['order_sn'] = $order_sn;
            $rs = $orderGroupModel->find($orderFindData);
        } while ($rs['status'] == 200);

        //生成商城订单
        if (!isset($params['remark'])) {
            $params['remark'] = "";
        }
        //总计  商品+运费
        $total_price = $total_price + $express_price['data'];
        $payment_money = $payment_money + $express_price['data'];
        // 查询用户是否是vip
        $userModel = new UserModel();
        $where['id'] = yii::$app->session['user_id'];
        $userInfo = $userModel->find($where);
        if ($userInfo['status'] != 200) {
            return result(500, '未找到此用户');
        }
        $discount_ratio = 1;
        if ($userInfo['data']['is_vip'] == 1 && $userInfo['data']['vip_validity_time'] >= time()) {
            //检测用户是否有开启的vip会员卡，防止商户禁用
            $vipAccessModel = new VipAccessModel();
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $user_id = yii::$app->session['user_id'];
            $where_ = "sva.`key` = '{$key}' 
            AND sva.merchant_id = {$merchant_id} 
            AND sva.user_id = {$user_id}
            AND sva.`status`=1
            AND sv.`status`=1";
            $sql = "SELECT sva.*,sv.`status` as sv_status FROM shop_vip_access as sva
                          LEFT JOIN shop_vip as sv ON sva.vip_id = sv.id WHERE  " . $where_;
            $list = $orderGroupModel->querySql($sql);
            if ($list) {
                $vipConfigModel = new VipConfigModel();
                $whereConfig['key'] = yii::$app->session['key'];
                $whereConfig['merchant_id'] = yii::$app->session['merchant_id'];
                $whereConfig['status'] = 1;
                $info = $vipConfigModel->one($whereConfig);
                $discount_ratio = $info['data']['discount_ratio'];
                $payment_money = bcmul($payment_money, $info['data']['discount_ratio'], 2); // 计算优惠打折
            }
        }
        if ($payment_money <= 0) {
            $payment_money = 0.01;
        }

        $order = array(
            '`key`' => $params['`key`'],
            'merchant_id' => yii::$app->session['merchant_id'],
            'partner_id' => $params['partner_id'] ?? 0,
            'user_id' => yii::$app->session['user_id'],
            'goodsname' => $name,
            'order_sn' => $order_sn,
            'transaction_order_sn' => $order_sn,
            'user_contact_id' => $user_contact_id,
            'address' => $address,
            'phone' => $contactData['data']['phone'],
            'name' => $contactData['data']['name'],
            'total_price' => $total_price,
            'payment_money' => $payment_money,
            'voucher_id' => $voucher_id,
            'express_price' => $express_price['data'],
            'after_sale' => -1,
            'status' => 0,
            'remark' => $params['remark'],
            'create_time' => time(),
            'is_assemble' => 1,
            'express_type' => $params['type'],
            'service_goods_status' => $service_goods_status,
        );


        $configModel = new \app\models\tuan\ConfigModel();

        $leaderModel = new \app\models\tuan\UserModel;
        $leaderData = $leaderModel->do_one(['uid' => yii::$app->session['user_id']]);
        if ($leaderData['status'] == 200) {
            $order['leader_uid'] = $leaderData['data']['leader_uid'];
        } else if ($leaderData['status'] == 204) {
            $tuanUser = array(
                'key' => yii::$app->session['key'],
                'merchant_id' => yii::$app->session['merchant_id'],
                'uid' => yii::$app->session['user_id'],
                'is_verify' => 0,
                'leader_uid' => $params['leader_id'],
                'status' => 1,
            );
            $tuanUserModel = new \app\models\tuan\UserModel();
            $tuanUserModel->do_add($tuanUser);
        } else {
            return $leaderData;
        }
        $config = $configModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);
        if ($config['status'] == 200 && $config['data']['status'] == 1) {
            $order['is_tuan'] = 1;
            $order['tuan_status'] = 0;
            $order['leader_self_uid'] = $params['leader_id'];
        }
        //生成子订单
        /**
         * 优惠后的金额  10-10/100*40   总金额减去优惠金额
         */
        if ($voucherData == FALSE) {
            for ($i = 0; $i < count($subGoods); $i++) {
                $pay_price = bcmul($subGoods[$i]['total_price'], $discount_ratio, 2);
                $subGoods[$i]['payment_money'] = $pay_price <= 0 ? 0.01 : $pay_price; // 计算优惠打折;
                $subGoods[$i]['order_group_sn'] = $order_sn;
                $subGoods[$i]['merchant_id'] = yii::$app->session['merchant_id'];
                $subGoods[$i]['`key`'] = $params['`key`'];
                $subGoods[$i]['user_id'] = yii::$app->session['user_id'];
            }
        } else {
            for ($i = 0; $i < count($subGoods); $i++) {
                $pay_price = bcmul(($subGoods[$i]['total_price'] - ($voucherData['data']['price'] / $total_price * $subGoods[$i]['total_price'])), $discount_ratio, 2);
                $subGoods[$i]['payment_money'] = $pay_price <= 0 ? 0.01 : $pay_price;
                $subGoods[$i]['order_group_sn'] = $order_sn;
                $subGoods[$i]['merchant_id'] = yii::$app->session['merchant_id'];
                $subGoods[$i]['`key`'] = $params['`key`'];
                $subGoods[$i]['user_id'] = yii::$app->session['user_id'];
            }
        }
        //生成系统订单
        $systemPayModel = new PayModel();
        $systemPayData = array(
            'order_id' => $order_sn,
            'user_id' => yii::$app->session['user_id'],
            'merchant_id' => yii::$app->session['merchant_id'],
            'remain_price' => $payment_money,
            'type' => 3,
            'total_price' => $total_price,
            'status' => 2,
        );
        $orderModel = new SubOrderModel();
        //优惠券锁定
        $voucherModel = new VoucherModel();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($voucherData != false) {
                $voucherModel->update(['id' => $params['voucher_id'], 'status' => 0]);
            }
            //提交订单
            $order_res = $orderGroupModel->add($order);
            $systemPayModel->add($systemPayData);
            for ($i = 0; $i < count($subGoods); $i++) {
                $orderModel->add($subGoods[$i]);
            }
            //创建一个拼团订单
            $groupAccessOrder = new ShopAssembleAccessModel();
            if ($params['group_id']) {
                $groupOrderInfo = $groupAccessOrder->one(['id' => $params['group_id']]);
                if ($groupOrderInfo['status'] == 200) {
                    $params['group_id'] = $groupOrderInfo['data']['id'];
                    $expire_time = $groupOrderInfo['data']['expire_time'];
                } else {
                    $params['group_id'] = 0;
                    $expire_time = time() + 86400;
                }
            } else {
                $expire_time = time() + 86400;
            }
            $groupOrder['key'] = $params['`key`'];
            $groupOrder['merchant_id'] = $systemPayData['merchant_id'];
            $groupOrder['goods_id'] = $params['goods'][0]['list'][0]['goods_id'];
            $groupOrder['uid'] = $systemPayData['user_id'];
            $groupOrder['leader_id'] = $params['group_id'];
            $groupOrder['order_sn'] = $order_sn;
            $groupOrder['is_leader'] = $params['group_id'] == 0 ? 1 : 0;
            $groupOrder['type'] = $groupInfo['data']['type'];
            $groupOrder['expire_time'] = $expire_time;
            $groupOrder['number'] = $params['number'];
            $groupOrder['price'] = $payment_money;
            $groupOrder['status'] = 1;
            $res_add = $groupAccessOrder->add($groupOrder);
            if ($params['group_id']) {
                $order['group_id'] = $params['group_id'];
            } else {
                $order['group_id'] = $res_add['data'];
            }
            //当前拼团人数（拼团订单，拼团中的是无法退款的）
            $groupModel = new ShopAssembleAccessModel();
            $total = $groupModel->get_count(['leader_id' => $order['group_id'], 'key' => $params['`key`']]);
            $order['group_number'] = $total + 1;
            $transaction->commit();
            return result(200, '请求成功', $order);
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "订单提交失败！");
        }
    }

    //商城普通商品 订单
    public function shopOrder($params, $service_goods_status = 0)
    {
        $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
        $comboAccessData = $comboAccessModel->do_one(['<>' => ['order_remain_number', 0], '>' => ['validity_time', time()], 'orderby' => 'id asc', 'merchant_id' => yii::$app->session['merchant_id']]);

        if ($comboAccessData['status'] != 200) {
            return result(500, "下单失败,商户信息错误");
        }
        if ($comboAccessData['data']['order_remain_number'] < 1) {
            return result(500, "商户订单数量不足，下单失败");
        }

        $params['goods'] = json_decode($params['goods'], true);
        $params['`key`'] = yii::$app->session['key'];
        /**
         * 查询优惠券
         */
        $voucherModel = new VoucherModel();
        $voucherParams['user_id'] = yii::$app->session['user_id'];
        $user_id = yii::$app->session['user_id'];
        $voucherParams['merchant_id'] = yii::$app->session['merchant_id'];
        if (isset($params['voucher_id'])) {
            if ($params['voucher_id'] != "") {
                $voucherData['id'] = $params['voucher_id'];
                $voucherData = $voucherModel->find($voucherData);
                // $params['user_id'] = yii::$app->session['user_id'];
                if ($voucherData['status'] != 200) {
                    return result(500, "该优惠券已使用，或已失效！");
                }
            } else {
                $voucherData = false;
            }
        } else {
            $voucherData = false;
        }

        $payment_money = 0;

        /**
         * 计算商品总价格 商品名称拼接
         */
        $stockModel = new StockModel();
        $goodModel = new GoodsModel();
        $total_price = 0;
        $name = "";
        $subGoods = array();
        $number = 0;
        // $estimated_time = 0;

        $user_id = yii::$app->session['user_id'];
        $merchant_id = yii::$app->session['merchant_id'];
        $key = yii::$app->session['key'];
        $orderGroupModel = new OrderModel();
        $supplier_id = 0;

        for ($i = 0; $i < count($params['goods']); $i++) {
            $stockData = $stockModel->find(['id' => $params['goods'][$i]['stock_id']]);
            $goodData = $goodModel->find(['id' => $params['goods'][$i]['goods_id']]);
            if ($goodData['status'] != 200 && $stockData['status'] != 200) {
                return result(500, "找不到该商品或商品已下架");
            }
            if ($goodData['data']['supplier'] != 0) {
                return result(500, "供应商商品请单独下单");
            }
            if (count($params['goods']) == 1 && $goodData['data']['type'] == 3 && $goodData['data']['service_goods_is_ship'] == 1) {
                $service_goods_status = 1;
            }
            if ($goodData['data']['is_limit'] == 1 && $goodData['data']['limit_number'] > 0) { // 检测此商品被购买了多少次
                $sql = "SELECT sum(so.number) as total FROM shop_order_group as sog
                          LEFT JOIN shop_order as so ON sog.order_sn = so.order_group_sn WHERE  so.goods_id = {$params['goods'][$i]['goods_id']} and sog.`status` in  (0,1,3,5,6,7) and sog.user_id = {$user_id} ";
                $total = $orderGroupModel->querySql($sql);
                if ((int)$total[0]['total'] >= (int)$goodData['data']['limit_number']) {
                    return result(500, "此商品已限量了！");
                }
            }
//            if ($stockData['data']['number'] == 0) {
//                return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}已售罄!");
//            } else if ($stockData['data']['number'] < $params['goods'][$i]['number']) {
//                return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}购买数量超出库存!");
//            }

            $time = time();
            $sql = "SELECT * FROM `shop_flash_sale_group` where FIND_IN_SET({$params['goods'][$i]['goods_id']},goods_ids) and start_time <={$time} and end_time >={$time} and `key` = '{$key}' and merchant_id = {$merchant_id} and delete_time is null;";
            $res = yii::$app->db->createCommand($sql)->queryAll();

            if (count($res) == 0) {
                if ($stockData['data']['number'] == 0) {
                    return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}已售罄!");
                } else if ($stockData['data']['number'] < $params['goods'][$i]['number']) {
                    return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}购买数量超出库存!");
                }
                $subGoods[$i]['price'] = $stockData['data']['price'];
                $subGoods[$i]['is_flash_sale'] = 0;
            } else {
                $sql = "SELECT * FROM `shop_flash_sale` where goods_id = {$params['goods'][$i]['goods_id']} and delete_time is not null ";
                $res = yii::$app->db->createCommand($sql)->queryAll();
                $property = explode("-", $res[0]['property']);
                for ($k = 0; $k < count($property); $k++) {
                    $a = json_decode($property[$k], true);
                    if ($stockData['data']['id'] == $a['stock_id']) {
                        if ($a['stocks'] == 0) {
                            return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}已售罄!");
                        } else if ($a['stocks'] < $params['goods'][$i]['number']) {
                            return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}购买数量超出库存!");
                        }
                        $subGoods[$i]['price'] = $a['flash_price'];
                        $stockData['data']['price'] = $a['flash_price'];
                    }

                }
                $subGoods[$i]['is_flash_sale'] = 1;
            }

            //砍价
            if (isset($params['bargin_id'])) {
                if ($goodData['data']['is_bargain'] == 1) {
                    $bargainModel = new ShopBargainInfoModel();
                    $bargins = $bargainModel->do_one(['id' => $params['bargin_id'], 'goods_id' => $goodData['data']['id'], 'promoter_user_id' => yii::$app->session['user_id']]);
                    $barginInfo = $bargainModel->do_one(['orderby' => 'id desc', 'goods_id' => $goodData['data']['id'], 'promoter_user_id' => yii::$app->session['user_id'], 'promoter_sn' => $bargins['data']['promoter_sn']]);
                    $subGoods[$i]['price'] = $barginInfo['goods_price'];
                    $stockData['data']['price'] = $barginInfo['goods_price'];
                }
            }

            if ($i == 0) {
                $total_price = $stockData['data']['price'] * $params['goods'][$i]['number'];
                $name = $goodData['data']['name'];
            } else {
                $total_price = $total_price + $stockData['data']['price'] * $params['goods'][$i]['number'];
                $name = $name . "," . $goodData['data']['name'];
            }
            $number = $number + $params['goods'][$i]['number'];
            //子订单数据
            $supplier_id = $goodData['data']['supplier_id'];
            $subGoods[$i]['goods_id'] = $goodData['data']['id'];
            $subGoods[$i]['stock_id'] = $stockData['data']['id'];
            $subGoods[$i]['pic_url'] = $stockData['data']['pic_url'];
            $subGoods[$i]['name'] = $goodData['data']['name'];
            $subGoods[$i]['number'] = $params['goods'][$i]['number'];
            //     $subGoods[$i]['price'] = $stockData['data']['price'];
            //$subGoods[$i]['estimated_time'] = $estimated_time;
            $subGoods[$i]['total_price'] = $stockData['data']['price'] * $params['goods'][$i]['number'];
            $subGoods[$i]['property1_name'] = isset($params['goods'][$i]['property1_name']) ? $params['goods'][$i]['property1_name'] : "";
            $subGoods[$i]['property2_name'] = isset($params['goods'][$i]['property2_name']) ? $params['goods'][$i]['property2_name'] : "";
        }
        if ($voucherData == FALSE) {
            $payment_money = $total_price;
        } else {
            if ($voucherData['data']['full_price'] == 0 || $voucherData['data']['full_price'] <= $total_price) {
                $payment_money = $total_price - $voucherData['data']['price'];
            } else {
                return result(500, "该优惠券未达到使用标准！");
            }
        }


        $voucher_id = $voucherData['data']['id'];
        //收货地址
        $contactModel = new ContactModel();
        if (!isset($params['user_contact_id'])) {
            return result(500, '请填写收货地址');
        }
        $contactParams['id'] = $params['user_contact_id'];
        $contactParams['user_id'] = yii::$app->session['user_id'];
        $contactData = $contactModel->find($contactParams);
        if ($contactData['status'] != 200) {
            return result(500, '未找到该收货地址');
        }
        $user_contact_id = $contactData['data']['id'];
        //快递费

        $express_price = $this->Kdf($contactData['data']['id'], $number);

        //查询订单唯一
        do {
            $order_sn = order_sn();
            $orderFindData['order_sn'] = $order_sn;
            $rs = $orderGroupModel->find($orderFindData);
        } while ($rs['status'] == 200);

        //生成商城订单
        if (!isset($params['remark'])) {
            $params['remark'] = "";
        }
        //总计  商品+运费
        $total_price = $total_price + $express_price;
        $payment_money = $payment_money + $express_price;
        // 查询用户是否是vip
        $userModel = new UserModel();
        $where['id'] = yii::$app->session['user_id'];
        $userInfo = $userModel->find($where);
        if ($userInfo['status'] != 200) {
            return result(500, '未找到此用户');
        }
        $discount_ratio = 1;
        if ($userInfo['data']['is_vip'] == 1 && $userInfo['data']['vip_validity_time'] >= time()) {
            //检测用户是否有开启的vip会员卡，防止商户禁用
            $vipAccessModel = new VipAccessModel();
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $user_id = yii::$app->session['user_id'];
            $where_ = "sva.`key` = '{$key}' 
            AND sva.merchant_id = {$merchant_id} 
            AND sva.user_id = {$user_id}
            AND sva.`status`=1
            AND sv.`status`=1";
            $sql = "SELECT sva.*,sv.`status` as sv_status FROM shop_vip_access as sva
                          LEFT JOIN shop_vip as sv ON sva.vip_id = sv.id WHERE  " . $where_;
            $list = $orderGroupModel->querySql($sql);
            if ($list) {
                $vipConfigModel = new VipConfigModel();
                $whereConfig['key'] = yii::$app->session['key'];
                $whereConfig['merchant_id'] = yii::$app->session['merchant_id'];
                $whereConfig['status'] = 1;
                $info = $vipConfigModel->one($whereConfig);
                $discount_ratio = $info['data']['discount_ratio'];
                $payment_money = bcmul($payment_money, $info['data']['discount_ratio'], 2); // 计算优惠打折
            }
        }

        $order = array(
            '`key`' => $params['`key`'],
            'merchant_id' => yii::$app->session['merchant_id'],
            'user_id' => yii::$app->session['user_id'],
            'goodsname' => $name,
            'order_sn' => $order_sn,
            'user_contact_id' => $user_contact_id,
            'address' => $contactData['data']['province'] . "-" . $contactData['data']['city'] . "-" . $contactData['data']['area'] . "-" . $contactData['data']['street'] . $contactData['data']['address'] . "-" . $contactData['data']['postcode'],
            'phone' => $contactData['data']['phone'],
            'name' => $contactData['data']['name'],
            'total_price' => $total_price,
            'payment_money' => $payment_money,
            'voucher_id' => $voucher_id,
            'express_price' => $express_price,
            'after_sale' => -1,
            'status' => 0,
            'remark' => $params['remark'],
            'supplier_id' => $supplier_id,
            'partner_id' => $params['partner_id'] ?? 0,
            'create_time' => time(),
            'service_goods_status' => $service_goods_status,
            'estimated_service_time' => isset($params['estimated_service_time']) ? $params['estimated_service_time'] : "",
            'is_assemble' => 0
        );
        //生成子订单
        /**
         * 优惠后的金额  10-10/100*40   总金额减去优惠金额
         */
        if ($voucherData == FALSE) {
            for ($i = 0; $i < count($subGoods); $i++) {
                $subGoods[$i]['payment_money'] = bcmul($subGoods[$i]['total_price'], $discount_ratio, 2); // 计算优惠打折;
                $subGoods[$i]['order_group_sn'] = $order_sn;
                $subGoods[$i]['merchant_id'] = yii::$app->session['merchant_id'];
                $subGoods[$i]['`key`'] = $params['`key`'];
                $subGoods[$i]['user_id'] = yii::$app->session['user_id'];
            }
        } else {
            for ($i = 0; $i < count($subGoods); $i++) {
                $subGoods[$i]['payment_money'] = bcmul(($subGoods[$i]['total_price'] - ($voucherData['data']['price'] / $total_price * $subGoods[$i]['total_price'])), $discount_ratio, 2);
                $subGoods[$i]['order_group_sn'] = $order_sn;
                $subGoods[$i]['merchant_id'] = yii::$app->session['merchant_id'];
                $subGoods[$i]['`key`'] = $params['`key`'];
                $subGoods[$i]['user_id'] = yii::$app->session['user_id'];
            }
        }

        //生成系统订单
        $systemPayModel = new PayModel();
        $systemPayData = array(
            'order_id' => $order_sn,
            'user_id' => yii::$app->session['user_id'],
            'merchant_id' => yii::$app->session['merchant_id'],
            'remain_price' => $payment_money,
            'type' => 3,
            'total_price' => $total_price,
            'status' => 2,
        );
        //

        $orderModel = new SubOrderModel();
        //优惠券锁定
        $voucherModel = new VoucherModel();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($voucherData != false) {
                $voucherModel->update(['id' => $params['voucher_id'], 'status' => 0]);
            }
            //提交订单
            $orderGroupModel->add($order);
            $systemPayModel->add($systemPayData);
            for ($i = 0; $i < count($subGoods); $i++) {
                $orderModel->add($subGoods[$i]);
            }

            $cartModel = new CartModel();
            //删除购物车商品
            for ($i = 0; $i < count($params['goods']); $i++) {
                if (isset($params['goods'][$i]['id'])) {
                    $cartModel->delete(['id' => $params['goods'][$i]['id']]);
                }
            }
            $comboAccessModel->do_update(['id' => $comboAccessData['data']['id']], ['order_remain_number' => $comboAccessData['data']['order_remain_number'] - 1]);
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            return result(200, '请求成功', $order);
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "订单提交失败！");
        }
    }

    public function actionRandom()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $orderModel = new OrderModel();
            $sql = "select order_sn,shop_user.nickname,shop_user.avatar from shop_order_group inner join shop_user on shop_order_group.user_id = shop_user.id where shop_order_group.`status`  !=0 and shop_order_group.`key`='{$key}' and shop_order_group.merchant_id = {$merchant_id}  group by shop_user.id  ORDER BY RAND() LIMIT 10 ";
            $array = $orderModel->querySql($sql);
            return result(200, '请求成功', $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    //团购订单
    public function tuanOrder($params, $service_goods_status = 0)
    {
        $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
        $comboAccessData = $comboAccessModel->do_one(['<>' => ['order_remain_number', 0], '>' => ['validity_time', time()], 'orderby' => 'id asc', 'merchant_id' => yii::$app->session['merchant_id']]);

        if ($comboAccessData['status'] != 200) {
            return result(500, "下单失败,商户未购买订单套餐");
        }
        if ($comboAccessData['data']['order_remain_number'] < 1) {
            return result(500, "商户订单数量不足，下单失败");
        }

        $params['goods'] = json_decode($params['goods'], true);
        $params['`key`'] = yii::$app->session['key'];
        /**
         * 查询优惠券
         */
        $voucherModel = new VoucherModel();
        $voucherParams['user_id'] = yii::$app->session['user_id'];
        $voucherParams['merchant_id'] = yii::$app->session['merchant_id'];
        if (isset($params['voucher_id'])) {
            if ($params['voucher_id'] != "") {
                $voucherData['id'] = $params['voucher_id'];
                $voucherData = $voucherModel->find($voucherData);
                // $params['user_id'] = yii::$app->session['user_id'];
                if ($voucherData['status'] != 200) {
                    return result(500, "该优惠券已使用，或已失效！");
                }
            } else {
                $voucherData = false;
            }
        } else {
            $voucherData = false;
        }

        $payment_money = 0;

        /**
         * 计算商品总价格 商品名称拼接
         */
        $stockModel = new StockModel();
        $goodModel = new GoodsModel();
        $orderGroupModel = new OrderModel();
        $total_price = 0;
        $name = "";
        $subGoods = array();
        $number = 0;
        $user_id = yii::$app->session['user_id'];
        $merchant_id = yii::$app->session['merchant_id'];
        $key = yii::$app->session['key'];
        $phone = "";
        $name = "";
        $supplier_id = 0;
        for ($i = 0; $i < count($params['goods']); $i++) {
            $stockData = $stockModel->find(['id' => $params['goods'][$i]['stock_id']]);
            $goodData = $goodModel->find(['id' => $params['goods'][$i]['goods_id']]);
            if ($goodData['status'] != 200 && $stockData['status'] != 200) {
                return result(500, "找不到该商品或商品已下架");
            }

            if ($goodData['data']['start_time'] > time()) {
                return result(500, "该商品未开始售卖");
            }
            if (count($params['goods']) == 1 && $goodData['data']['type'] == 3 && $goodData['data']['service_goods_is_ship'] == 1) {
                $service_goods_status = 1;
            }
            if ($goodData['data']['is_limit'] == 1 && $goodData['data']['limit_number'] > 0) { // 检测此商品被购买了多少次
                $sql = "SELECT sum(so.number) as total FROM shop_order_group as sog
                          LEFT JOIN shop_order as so ON sog.order_sn = so.order_group_sn WHERE  so.goods_id = {$params['goods'][$i]['goods_id']} and sog.`status` in  (0,1,3,5,6,7) and sog.user_id = {$user_id} ";
                $total = $orderGroupModel->querySql($sql);
                if ((int)$total[0]['total'] >= (int)$goodData['data']['limit_number']) {
                    return result(500, "此商品已限量了！");
                }
            }
            $time = time();
            $sql = "SELECT * FROM `shop_flash_sale_group` where FIND_IN_SET({$params['goods'][$i]['goods_id']},goods_ids) and start_time <={$time} and end_time >={$time} and `key` = '{$key}' and merchant_id = {$merchant_id} and delete_time is null;";
            $res = yii::$app->db->createCommand($sql)->queryAll();


            if (count($res) == 0 && $stockData['status'] == 200) {
                if ($stockData['data']['number'] == 0) {
                    return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}已售罄!");
                } else if ($stockData['data']['number'] < $params['goods'][$i]['number']) {
                    return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}购买数量超出库存!");
                }
                $subGoods[$i]['price'] = $stockData['data']['price'];
                $subGoods[$i]['is_flash_sale'] = 0;
            } else {
                $sql = "SELECT * FROM `shop_flash_sale` where goods_id = {$params['goods'][$i]['goods_id']} and delete_time is null";
                $res = yii::$app->db->createCommand($sql)->queryAll();
                $property = explode("-", $res[0]['property']);

                for ($k = 0; $k < count($property); $k++) {
                    $a = json_decode($property[$k], true);

                    if ($stockData['data']['id'] == $a['stock_id']) {
                        if ($a['stocks'] == 0) {
                            return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}已售罄!");
                        } else if ($a['stocks'] < $params['goods'][$i]['number']) {
                            return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}购买数量超出库存!");
                        }
                        $subGoods[$i]['price'] = $a['flash_price'];
                        $stockData['data']['price'] = $a['flash_price'];

                    }
                }
                $subGoods[$i]['is_flash_sale'] = 1;
            }
            //砍价
//            $sql = "select * from shop_order_group where is_bargain =1 and goods_id = {$goodData['data']['id']} and status = 0 and user_id={$user_id}";
//            $bargain  = yii::$app->db->createCommand($sql)->queryAll();
//            if(count($bargain)==0){
            if (isset($params['bargin_id'])) {
                if ($goodData['data']['is_bargain'] == 1) {
                    $bargainModel = new ShopBargainInfoModel();
                    $bargins = $bargainModel->do_one(['id' => $params['bargin_id'], 'goods_id' => $goodData['data']['id'], 'promoter_user_id' => yii::$app->session['user_id']]);
                    $barginInfo = $bargainModel->do_one(['orderby' => 'id desc', 'goods_id' => $goodData['data']['id'], 'promoter_user_id' => yii::$app->session['user_id'], 'promoter_sn' => $bargins['data']['promoter_sn']]);
                    $subGoods[$i]['price'] = $barginInfo['data']['goods_price'];
                    $stockData['data']['price'] = $barginInfo['data']['goods_price'];
                }
            }
//            }else{
//                return result(500, "已有正在砍价切并未付款的订单。请付款后或者关闭订单 在重新发起砍价");
//            }


            if ($i == 0) {
                $total_price = $stockData['data']['price'] * $params['goods'][$i]['number'];
                $name = $goodData['data']['name'];
            } else {
                $total_price = $total_price + $stockData['data']['price'] * $params['goods'][$i]['number'];
                $name = $name . "," . $goodData['data']['name'];
            }
            $number = $number + $params['goods'][$i]['number'];
            //子订单数据

            $supplier_id = $goodData['data']['supplier_id'];
            $subGoods[$i]['goods_id'] = $goodData['data']['id'];
            $subGoods[$i]['stock_id'] = $stockData['data']['id'];
            $subGoods[$i]['pic_url'] = $stockData['data']['pic_url'];
            $subGoods[$i]['name'] = $goodData['data']['name'];
            $subGoods[$i]['number'] = $params['goods'][$i]['number'];
            $subGoods[$i]['total_price'] = $stockData['data']['price'] * $params['goods'][$i]['number'];
            $subGoods[$i]['property1_name'] = isset($params['goods'][$i]['property1_name']) ? $params['goods'][$i]['property1_name'] : "";
            $subGoods[$i]['property2_name'] = isset($params['goods'][$i]['property2_name']) ? $params['goods'][$i]['property2_name'] : "";
        }

        if ($voucherData == FALSE) {
            $payment_money = $total_price;
        } else {
            if ($voucherData['data']['full_price'] == 0 || $voucherData['data']['full_price'] <= $total_price) {
                $payment_money = $total_price - $voucherData['data']['price'];
            } else {
                return result(500, "该优惠券未达到使用标准！");
            }
        }


        $voucher_id = $voucherData['data']['id'];
        //收货地址

        // if ($params['type'] == 0) {
        if (isset($params['user_contact_id'])) {
            $contactModel = new ContactModel();
            if (!isset($params['user_contact_id'])) {
                return result(500, '请填写收货地址');
            }
            $contactParams['id'] = $params['user_contact_id'];
            $contactParams['user_id'] = yii::$app->session['user_id'];
            $contactData = $contactModel->find($contactParams);
            if ($contactData['status'] != 200) {
                return result(500, '未找到该收货地址');
            }
            $user_contact_id = $contactData['data']['id'];
            $address = $contactData['data']['province'] . "-" . $contactData['data']['city'] . "-" . $contactData['data']['area'] . "-" . $contactData['data']['street'] . $contactData['data']['address'] . "-" . $contactData['data']['postcode'];
            $phone = $contactData['data']['phone'];
            $name = $contactData['data']['name'];
        } else {
            $user_contact_id = 0;
            $address = "团长订单";
            $phone = $params['phone'];
            $name = $params['name'];
        }

//        } else {
//
//
//        }

        //快递费

        $express_price = 0.00;

        if ($params['type'] == 0) { // 快递
            $express_price = $this->Kdf($contactData['data']['id'], $number);
        } else if ($params['type'] == 1) { // 自提
            $express_price = 0;
        } else if ($params['type'] == 2) { // 团长配送
            $express_price = 0;
            $tuanLeaderModel = new \app\models\tuan\LeaderModel();
            $lerder = $tuanLeaderModel->do_one(['uid' => $params['leader_id']]);
            if ($lerder['status'] != 200) {
                return $lerder;
            }
            if ($lerder['data']['is_tuan_express'] == 0) {
                return result(500, "该团在未开启配送");
            }
            $express_price = $lerder['data']['tuan_express_fee'];
        }


        //查询订单唯一
        do {
            $order_sn = order_sn();
            $orderFindData['order_sn'] = $order_sn;
            $rs = $orderGroupModel->find($orderFindData);
        } while ($rs['status'] == 200);

        //生成商城订单
        if (!isset($params['remark'])) {
            $params['remark'] = "";
        }
        //总计  商品+运费
        $total_price = $total_price + $express_price;
        $payment_money = $payment_money + $express_price;
        // 查询用户是否是vip
        $userModel = new UserModel();
        $where['id'] = yii::$app->session['user_id'];
        $userInfo = $userModel->find($where);
        if ($userInfo['status'] != 200) {
            return result(500, '未找到此用户');
        }
        $discount_ratio = 1;
        if ($userInfo['data']['is_vip'] == 1 && $userInfo['data']['vip_validity_time'] >= time()) {
            //检测用户是否有开启的vip会员卡，防止商户禁用
            $vipAccessModel = new VipAccessModel();
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $user_id = yii::$app->session['user_id'];
            $where_ = "sva.`key` = '{$key}' 
            AND sva.merchant_id = {$merchant_id} 
            AND sva.user_id = {$user_id}
            AND sva.`status`=1
            AND sv.`status`=1";
            $sql = "SELECT sva.*,sv.`status` as sv_status FROM shop_vip_access as sva
                          LEFT JOIN shop_vip as sv ON sva.vip_id = sv.id WHERE  " . $where_;
            $list = $orderGroupModel->querySql($sql);
            if ($list) {
                $vipConfigModel = new VipConfigModel();
                $whereConfig['key'] = yii::$app->session['key'];
                $whereConfig['merchant_id'] = yii::$app->session['merchant_id'];
                $whereConfig['status'] = 1;
                $info = $vipConfigModel->one($whereConfig);
                $payment_money = bcmul($payment_money, $info['data']['discount_ratio'], 2); // 计算优惠打折
                $discount_ratio = $info['data']['discount_ratio'];
            }
        }
        $order = array(
            '`key`' => $params['`key`'],
            'merchant_id' => yii::$app->session['merchant_id'],
            'user_id' => yii::$app->session['user_id'],
            'goodsname' => $name,
            'order_sn' => $order_sn,
            'user_contact_id' => $user_contact_id,
            'address' => $address,
            'phone' => $phone,
            'name' => $name,
            'tuan_status' => 1,
            'total_price' => $total_price,
            'payment_money' => $payment_money,
            'voucher_id' => $voucher_id,
            'express_price' => $express_price,
            'express_type' => $params['type'],
            'after_sale' => -1,
            'status' => 0,
            'remark' => $params['remark'],
            'supplier_id' => $supplier_id,
            'partner_id' => $params['partner_id'] ?? 0,
            'create_time' => time(),
            'service_goods_status' => $service_goods_status,
            'estimated_service_time' => isset($params['estimated_service_time']) ? $params['estimated_service_time'] : "",
            'is_assemble' => 0
        );

        $configModel = new \app\models\tuan\ConfigModel();

        $leaderModel = new \app\models\tuan\UserModel;
        $leaderData = $leaderModel->do_one(['uid' => yii::$app->session['user_id']]);
        if ($leaderData['status'] == 200) {
            $order['leader_uid'] = $leaderData['data']['leader_uid'];
        } else if ($leaderData['status'] == 204) {
            $tuanUser = array(
                'key' => yii::$app->session['key'],
                'merchant_id' => yii::$app->session['merchant_id'],
                'uid' => yii::$app->session['user_id'],
                'is_verify' => 0,
                'leader_uid' => $params['leader_id'],
                'status' => 1,
            );
            $tuanUserModel = new \app\models\tuan\UserModel();
            $tuanUserModel->do_add($tuanUser);
        } else {
            return $leaderData;
        }
        $config = $configModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);
        if ($config['status'] == 200 && $config['data']['status'] == 1) {
            $order['is_tuan'] = 1;
            $order['tuan_status'] = 0;
            $order['leader_self_uid'] = $params['leader_id'];
        }

        //生成子订单
        /**
         * 优惠后的金额  10-10/100*40   总金额减去优惠金额
         */
        if ($voucherData == FALSE) {
            for ($i = 0; $i < count($subGoods); $i++) {
                $subGoods[$i]['payment_money'] = bcmul($subGoods[$i]['total_price'], $discount_ratio, 2); // 计算优惠打折;
                $subGoods[$i]['order_group_sn'] = $order_sn;
                $subGoods[$i]['merchant_id'] = yii::$app->session['merchant_id'];
                $subGoods[$i]['`key`'] = $params['`key`'];
                $subGoods[$i]['user_id'] = yii::$app->session['user_id'];
            }
        } else {
            for ($i = 0; $i < count($subGoods); $i++) {
                $subGoods[$i]['payment_money'] = bcmul(($subGoods[$i]['total_price'] - ($voucherData['data']['price'] / $total_price * $subGoods[$i]['total_price'])), $discount_ratio, 2);
                $subGoods[$i]['order_group_sn'] = $order_sn;
                $subGoods[$i]['merchant_id'] = yii::$app->session['merchant_id'];
                $subGoods[$i]['`key`'] = $params['`key`'];
                $subGoods[$i]['user_id'] = yii::$app->session['user_id'];
            }
        }

        //生成系统订单
        $systemPayModel = new PayModel();
        $systemPayData = array(
            'order_id' => $order_sn,
            'user_id' => yii::$app->session['user_id'],
            'merchant_id' => yii::$app->session['merchant_id'],
            'remain_price' => $payment_money,
            'type' => 3,
            'total_price' => $total_price,
            'status' => 2,
        );
        //

        $orderModel = new SubOrderModel();
        //优惠券锁定
        $voucherModel = new VoucherModel();
        $transaction = Yii::$app->db->beginTransaction();
        try {

            if ($voucherData != false) {
                $voucherModel->update(['id' => $params['voucher_id'], 'status' => 0]);
            }
            //提交订单商户信息有误
            $orderGroupModel->add($order);
            $systemPayModel->add($systemPayData);
            for ($i = 0; $i < count($subGoods); $i++) {
                $orderModel->add($subGoods[$i]);
            }

            $cartModel = new CartModel();
            //删除购物车商品
            for ($i = 0; $i < count($params['goods']); $i++) {
                if (isset($params['goods'][$i]['id'])) {
                    $cartModel->delete(['id' => $params['goods'][$i]['id']]);
                }
            }
            $comboAccessModel->do_update(['id' => $comboAccessData['data']['id']], ['order_remain_number' => $comboAccessData['data']['order_remain_number'] - 1]);
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            return result(200, '请求成功', $order);
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "订单提交失败！");
        }
    }

    /**
     * 拼团订单处理
     * @return bool
     */
    public function actionGroupOrderProcess()
    {
        try {
            $page = 0;
            while (true) {
                //查询拼团中的订单
                $orderModel = new OrderModel();
                $groupOrderModel = new ShopAssembleAccessModel();
                $groupConfigModel = new ShopAssembleModel();
                $sql = "SELECT shop_order_group.*  FROM shop_order_group
                          LEFT JOIN shop_assemble_access ON shop_assemble_access.order_sn = shop_order_group.order_sn WHERE  shop_order_group.status = 11 and shop_assemble_access.leader_id = 0 and  shop_assemble_access.is_leader = 1 	LIMIT {$page},100";
                $orderList = $orderModel->querySql($sql);
                if (!empty($orderList)) {
                    foreach ($orderList as $k => $val) {
                        //查询拼团订单表对应数据
                        $groupOrderModel = new ShopAssembleAccessModel();
                        $groupInfo = $groupOrderModel->one(['order_sn' => $val['order_sn']]);
                        if ($groupInfo['status'] != 200) {
                            file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . '查询不到拼团订单' . PHP_EOL, FILE_APPEND);
                            continue;
                        }
                        if ($groupInfo['data']['leader_id'] > 0 && $groupInfo['data']['is_leader'] == 0) { // 不是开团人的订单
                            //查询开团人的订单
                            $leaderInfo = $groupOrderModel->one(['id' => $groupInfo['data']['leader_id'], 'is_leader' => 1]);
                            if ($leaderInfo['status'] != 200) {
                                file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . '查询不到开团人的订单' . PHP_EOL, FILE_APPEND);
                                continue;
                            }
                            $number = $leaderInfo['data']['number'];
                            $leader_id = $leaderInfo['data']['id'];
                            $expire_time = $leaderInfo['data']['expire_time'];
                            $leader_order_sn = $leaderInfo['data']['order_sn'];
                        } elseif ($groupInfo['data']['leader_id'] == 0 && $groupInfo['data']['is_leader'] == 1) { // 是开团人订单
                            $number = $groupInfo['data']['number'];
                            $leader_id = $groupInfo['data']['id'];
                            $expire_time = $groupInfo['data']['expire_time'];
                            $leader_order_sn = $groupInfo['data']['order_sn'];
                        } else {
                            file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . '拼团订单不知道是开团还是参团' . PHP_EOL, FILE_APPEND);
                            continue;
                        }
                        //查看拼团配置
                        $configInfo = $groupConfigModel->one(['status' => 1, 'goods_id' => $groupInfo['data']['goods_id'], 'key' => $groupInfo['data']['key']]);
                        if ($configInfo['status'] != 200) {
                            file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . '开团配置找不到了' . PHP_EOL, FILE_APPEND);
                            continue;
                        }
                        $is_automatic = $configInfo['data']['is_automatic']; // 是否开启虚拟成团 过期时间五分钟时，0未开启 1 已开启
                        //查找当前团已经有多少人参加了（且订单状态是11)
                        $where['field'] = "shop_assemble_access.order_sn";
                        $where['shop_assemble_access.status'] = 1;
                        $where['shop_assemble_access.leader_id'] = $leader_id;
                        $where['shop_assemble_access.is_leader'] = 0;
                        $where['limit'] = 5000;
                        $where['shop_order_group.status'] = 11;
                        $where['join'][] = ['left join', 'shop_order_group', 'shop_order_group.order_sn = shop_assemble_access.order_sn'];
                        $order_sn_list = $groupOrderModel->do_select($where);
                        if ($order_sn_list['status'] != 200) {
                            $group_number = 1;
                        } else {
                            $group_number = count($order_sn_list['data']) + 1;
                        }
                        $temp_array = [];
                        if ($order_sn_list['status'] == 200) {
                            foreach ($order_sn_list['data'] as $v) {
                                $v = join(",", $v);
                                $temp_array[] = $v;
                            }
                            $temp_array[] = $leader_order_sn;
                        } else {
                            $temp_array[] = $val['order_sn'];
                        }
                        $str_order_sn = implode(",", $temp_array);
                        $now_time = time();
                        if ($number <= $group_number && $expire_time >= $now_time) { //拼成功了 修改订单状态
                            $status = 1;
                            if ($val['service_goods_status'] == 1) {
                                $status = 3;
                            }
                            $sql2 = "UPDATE shop_order_group SET `status` = {$status} where `order_sn` in ({$str_order_sn}) and `status`=11";
                            $res = yii::$app->db->createCommand($sql2)->execute();
                            if (!$res) {
                                file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . '拼成功更新失败' . PHP_EOL, FILE_APPEND);
                            }
                            continue;
                        } else { //如果不等于，查看过期时间是否已到，时间到了则拼团失败，未开启，则此团失败，已开启，则成功
                            if ($expire_time <= $now_time) { // 则关闭
                                $sql3 = "UPDATE shop_order_group SET `status` = 2 where `order_sn` in ({$str_order_sn}) and `status`=11";
                                $res = yii::$app->db->createCommand($sql3)->execute();
                                if (!$res) {
                                    file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . '关闭更新失败' . PHP_EOL, FILE_APPEND);
                                    continue;
                                }
                                // 执行退款
                                $new_order_sn = explode(',', $str_order_sn);
                                foreach ($new_order_sn as $order_sn) {
                                    $orderInfo_ = $orderModel->one(['order_sn' => $order_sn]);
                                    if ($orderInfo_['status'] != 200) {
                                        file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . '退款没找到订单' . PHP_EOL, FILE_APPEND);
                                        continue;
                                    }
                                    $res_refund = self::RefundMoney($order_sn, $orderInfo_['data']['key'], $orderInfo_['data']['merchant_id']);
                                    file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . '退款的订单' . $order_sn . ':' . json_encode($res_refund) . PHP_EOL, FILE_APPEND);

                                    if ($res_refund['result_code'] == "SUCCESS") {
                                        $data['status'] = 4;
                                        $data['order_sn'] = $order_sn;
                                        $data['refund'] = 'pintuan';
                                        $data['after_sale'] = 1;
                                        $orderModel->update($data);
                                        $balanceModel = new \app\models\shop\BalanceAccessModel();
                                        $balanceModel->do_update(['pay_sn' => $order_sn], ['status' => 2]);
                                    }
                                }
                            } else {
                                $time_diff = ($expire_time - $now_time);
                                if ($time_diff <= 300 && $is_automatic == 1) { //开启虚拟成团了
                                    $status = 1;
                                    if ($val['service_goods_status'] == 1) {
                                        $status = 3;
                                    }
                                    $sql5 = "UPDATE shop_order_group SET `status` = {$status} where `order_sn` in ({$str_order_sn}) and `status`=11";
                                    yii::$app->db->createCommand($sql5)->execute();
                                }
                            }
                        }
                    }
                } else {
                    return true;
                }
                $page++;
            }
        } catch (\Exception $e) {
            file_put_contents(Yii::getAlias('@webroot/') . '/group_order_error.text', date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * 退款
     * @param $order_sn
     * @param $key
     * @param $merchant_id
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \yii\db\Exception
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function RefundMoney($order_sn, $key, $merchant_id)
    {

        $params['order_sn'] = $order_sn;
        $params['merchant_id'] = $merchant_id;
        $params['`key`'] = $key;
        $orderModel = new OrderModel;
        $orderData = $orderModel->find($params);

        $payModel = new PayModel();

        $pays = $payModel->find(['order_id' => $order_sn]);

        //获取商户微信配置
        if ($orderData['data']['order_type'] == 1) {
            $config = $this->getSystemConfig($key, "wxpay", 1);
            $config['notify_url'] = "https://".$_SERVER['SERVER_NAME']."/api/web/index.php/pay/wechat/notifyreturn";
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $app = Factory::payment($config);
            // 参数分别为：微信订单号、商户退款单号、订单金额、退款金额、其他参数
            $res = $app->refund->byTransactionId($pays['data']['transaction_id'], $params['order_sn'], 1, 1, ['refund_desc' => '商品退款', 'notify_url' => "https://".$_SERVER['SERVER_NAME']."/api/web/index.php/pay/wechat/notifyreturn"]);
        } elseif ($orderData['data']['order_type'] == 3) { //余额退款
            $userModel = new UserModel();
            $userInfo = $userModel->find(['id' => $orderData['data']['user_id']]);
            if ($userInfo['status'] == 200) {
                $data['recharge_balance'] = bcadd($orderData['data']['payment_money'], $userInfo['data']['recharge_balance'], 2);
                $data['id'] = $orderData['data']['user_id'];
                $data['`key`'] = $orderData['data']['key'];
                $re_ = $userModel->update($data);
                if ($re_['status'] == 200) {
                    $res = ['result_code' => 'SUCCESS', 'result_msg' => 'yue'];
                } else {
                    $res = ['result_code' => 'FAIL'];
                }
            }
        } else {
            $config = self::getSystemConfig($key, "miniprogrampay", 1);
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            if ($config['wx_pay_type'] == 1) {
                $config['notify_url'] = "https://api.juanpao.com/pay/wechat/notifyreturn";
                $app = Factory::payment($config);
                // 参数分别为：微信订单号、商户退款单号、订单金额、退款金额、其他参数
                $res = $app->refund->byTransactionId($pays['data']['transaction_id'], $params['order_sn'], $orderData['data']['payment_money'] * 100, $orderData['data']['payment_money'] * 100, ['refund_desc' => '商品退款']);
            } else {
                $mini_pay = new \tools\pay\refund\Refund();
                $mini_pay->setPay_ver(Payx::PAY_VER);
                $mini_pay->setPay_type("010");
                $mini_pay->setService_id(Payx::SERVICE_ID);
                $mini_pay->setMerchant_no($config['merchant_no']);
                $mini_pay->setTerminal_id($config['terminal_id']);
                $mini_pay->setTerminal_trace($orderData['data']['order_sn']);
                $mini_pay->setTerminal_time(date("YmdHis"));
                $mini_pay->setRefund_fee($orderData['data']['payment_money'] * 100);
                $mini_pay->setOut_trade_no($pays['data']['transaction_id']);
                $pay_pre = Payx::refund($mini_pay, $config['saobei_access_token']);
                if ($pay_pre->return_code == "01") {
                    //修改当前订单的优惠卷状态改成0
                    $voucherModel = new \app\models\shop\VoucherModel();
                    $where['order_sn'] = $orderData['data']['order_sn'];
                    $where['status'] = 0;
                    $voucherModel->update($where);
                    $res = ['result_code' => 'SUCCESS', 'result_msg' => 'saobei'];
                } else {
                    $res = ['result_code' => 'FAIL'];
                }
            }
        }
        return $res;
    }

    /**
     * 当前用户拼团订单列表
     * @return array
     * @throws yii\db\Exception
     */
    public function actionGroupOrderList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (!isset($params['status'])) {
                $status = [11]; // 进行中
            } else {
                if ($params['status'] == 0) {
                    $status = [11]; // 进行中
                } elseif ($params['status'] == 1) {
                    $status = [1, 3, 5, 6, 7]; // 已经成团
                } else {
                    $status = [4];
                }
            }
            $groupOrderModel = new ShopAssembleAccessModel();
            $userModel = new UserModel();
            $subOrderModel = new SubOrderModel();
            $user_id = yii::$app->session['user_id'];
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $where['shop_assemble_access.key'] = $key;
            $where['shop_assemble_access.merchant_id'] = $merchant_id;
            $where['shop_assemble_access.uid'] = $user_id;
            $where['in'] = ['shop_order_group.status', $status];
            $where['field'] = "shop_assemble_access.*";
            $where['join'][] = ['left join', 'shop_order_group', 'shop_order_group.order_sn = shop_assemble_access.order_sn'];
            $list = $groupOrderModel->do_select($where);
            if ($list['status'] == 200) {
                foreach ($list['data'] as &$val) {
                    //检测此订单是否是开团订单
                    $temp_array = [];
                    if ($val['is_leader'] == 1 && $val['leader_id'] == 0) { // 是开团订单
                        $orderArr = $groupOrderModel->do_select(['leader_id' => $val['id'], 'field' => 'uid']);
                        $temp_array[] = $user_id;
                    } else {
                        $orderArr = $groupOrderModel->do_select(['leader_id' => $val['leader_id'], 'field' => 'uid']);
                    }
                    if ($orderArr['status'] == 200) {
                        foreach ($orderArr['data'] as $v) {
                            $v = join(",", $v);
                            $temp_array[] = $v;
                        }
                    }
                    $str_uid = implode(",", $temp_array);
                    $userList = $userModel->findall(["id in ({$str_uid})" => null, 'fields' => 'avatar']);
                    $val['user_list'] = [];
                    if ($userList['status'] == 200) {
                        $val['user_list'] = $userList['data'];
                    }
                    //查询商品信息
                    $goodsInfo = $subOrderModel->find(['order_sn' => $val['order_sn'],]);
                    $val['goods_info'] = [];
                    if ($goodsInfo['status'] == 200) {
                        $val['goods_info'] = $goodsInfo['data'];
                    }
                    if ($status == [11]) {
                        // 差几人团
                        $total = $groupOrderModel->get_count(['leader_id' => $val['id'], 'key' => $val['key']]);
                        $val['poor'] = bcsub($val['number'], $total + 1);
                    }
                }
            }
            $list['data'] = $list['data'] ?? [];
            return result(200, "请求成功", $list['data']);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOrder()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $goodsModel = new GoodsModel();
            $orderGroupModel = new OrderModel();

            $bool= getConfig(yii::$app->session['user_id'].'-order');
            if($bool==true){
                return result(500, "请稍后再试");
            }
            if (isset($params['group_type']) && $params['group_type'] == 1) {// 走去拼团
                if (!isset($params['number']) || empty($params['number'])) {
                    return result(500, "缺少拼团人数");
                }
                return $this->groupOrder($params);
            } else {
                $params['goods'] = json_decode($params['goods'], true);
                do {
                    $transaction_order_sn = "t_" . order_sn();
                    $orderFindData['transaction_order_sn'] = $transaction_order_sn;
                    $rs = $orderGroupModel->find($orderFindData);
                } while ($rs['status'] == 200);
                for ($i = 0; $i < count($params['goods']); $i++) {
                    $goods = $params['goods'][$i]['list'];

                    for ($j = 0; $j < count($goods); $j++) {
                        $goodData = $goodsModel->find(['id' => $goods[$j]['goods_id']]);
                        $type = 0;
                        if (count($params['goods']) == 1 && count($goods) == 1) {
                            if ($goodData['status'] != 200) {
                                return result(500, "找不到该商品或商品已下架");
                            }
                            if ($goodData['data']['is_open_assemble']) {
                                $type = 2; //平团订单;
                            }
                            if ($goodData['data']['is_bargain']) {
                                $type = 3; //砍价订单;
                            }
                        } else {
                            if ($goodData['status'] != 200) {
                                return result(500, "找不到该商品或商品已下架");
                            }
                            if ($goodData['data']['is_open_assemble']) {
                                return result(500, "拼团商品只能单独够买");
                            }
                            if ($goodData['data']['is_bargain']) {
                                return result(500, "砍价商品只能单独购买" . $goodData['data']['name']);
                            }
                            $type = 1;//购物车订单
                        }
                    }
                    $data['bargin_id'] = isset($params['bargin_id']) ? $params['bargin_id'] : "";
                    $data['estimated_service_time'] = isset($params['estimated_service_time']) ? $params['estimated_service_time'] : "";
                    $data['supplier_id'] = $params['goods'][$i]['supplier_id'];
                    $data['leader_id'] = $params['leader_id'];
                    $data['type'] = $params['type'];
                    $data['partner_id'] = $params['partner_id'] ?? 0;
                    $data['user_contact_id'] = isset($params['user_contact_id']) ? $params['user_contact_id'] : 0;
                    $data['voucher_id'] = isset($params['goods'][$i]['voucher_id']) ? $params['goods'][$i]['voucher_id'] : 0;
                    $data['remark'] = isset($params['goods'][$i]['remark']) ? $params['goods'][$i]['remark'] : "";
                    $data['transaction_order_sn'] = $transaction_order_sn;
                    if ($params['user_contact_id'] == 0) {
                        $data['name'] = $params['name'];
                        $data['phone'] = $params['phone'];
                    }
                    $data = $this->ptrder($goods, $data);//普通订单
                    if($data['status']==200){
                        for ($i = 0; $i < count($params['goods']); $i++) {
                            for ($j = 0; $j < count($goods); $j++) {
                                $goodData = $goodsModel->find(['id' => $goods[$j]['goods_id']]);
                                $cartModel = new CartModel();
                                $res = $cartModel->delete(['goods_id' => $goods[$j]['goods_id'],'user_id'=>yii::$app->session['user_id'],'key'=>yii::$app->session['key'],'merchant_id'=>yii::$app->session['merchant_id']]);

                            }
                        }
                    }
                }
                setConfig(yii::$app->session['user_id'].'-order',true,'20');
                return $data;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function ptrder($goods, $data)
    {
        if ($data['leader_id'] != 0) {
            $tuanConfigModel = new \app\models\tuan\ConfigModel();
            $tuanconfig = $tuanConfigModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);

            if ($tuanconfig['data']['status'] == 1) {
                $time = date("Y-m-d", time());
                if ($tuanconfig['data']['open_time'] + strtotime($time . " 00:00:00") <= time() && $tuanconfig['data']['close_time'] + strtotime($time . " 00:00:00") >= time()) {
                    return result(500, "团购未开市");
                }
            }
            $data['is_tuan'] = 1;

        } else {
            $data['is_tuan'] = 0;
        }

        $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
        $comboAccessData = $comboAccessModel->do_one(['<>' => ['order_remain_number', 0], '>' => ['validity_time', time()], 'orderby' => 'id asc', 'merchant_id' => yii::$app->session['merchant_id']]);


        if ($comboAccessData['status'] != 200) {

            return result(500, "下单失败,商户信息错误");
        }

        if ($comboAccessData['data']['order_remain_number'] < count($goods)) {
            return result(500, "商户订单数量不足，下单失败");
        }
        $data['combo_id'] = $comboAccessData['data']['id'];
        $data['combo_number'] = $comboAccessData['data']['order_remain_number'];
        $rs = $this->goods($goods, $data);
        if ($rs['status'] != 200) {
            return $rs;
        }
        $res = $rs['data'];
        if ($data['voucher_id'] != 0) {
            $is_voucher = $this->voucher($data['voucher_id'], $res['order']['total_price']);
            if ($is_voucher['status'] != 200) {
                return $is_voucher;
            }
            $res['order']['payment_money'] = $is_voucher['data'];
        }

        if ($data['supplier_id'] == 0) {
            $is_vip = $this->vip($res['order']['payment_money']);
            if ($is_vip['status'] != 200) {
                return $is_vip;
            }
            $res['order']['payment_money'] = $is_vip['data'];
        }
        //满减 是否包邮
        $appModel = new \app\models\admin\app\AppAccessModel();
        $app = $appModel->find(['merchant_id' => yii::$app->session['merchant_id'], '`key`' => yii::$app->session['key']]);
        // var_dump($app);die();
        $reduction_info = json_decode($app['data']['reduction_info'], true);
        //"reduction_achieve":["10","36","50","100","200"],"reduction_decrease":["3","5","10","20","30"],"free_shipping":["false","true","true","false","false"]
        if ($reduction_info['is_reduction'] == 1) {
            for ($i = 0; $i < count($reduction_info['reduction_achieve']); $i++) {
                // 第二层为从$i+1的地方循环到数组最后
                for ($j = $i + 1; $j < count($reduction_info['reduction_achieve']); $j++) {
                    // 比较数组中两个相邻值的大小
                    if ($reduction_info['reduction_achieve'][$i] < $reduction_info['reduction_achieve'][$j]) {
                        $tem = $reduction_info['reduction_achieve'][$i]; // 这里临时变量，存贮$i的值
                        $reduction_info['reduction_achieve'][$i] = $reduction_info['reduction_achieve'][$j]; // 第一次更换位置
                        $reduction_info['reduction_achieve'][$j] = $tem; // 完成位置互换

                        $tem1 = $reduction_info['reduction_decrease'][$i]; // 这里临时变量，存贮$i的值
                        $reduction_info['reduction_decrease'][$i] = $reduction_info['reduction_decrease'][$j]; // 第一次更换位置
                        $reduction_info['reduction_decrease'][$j] = $tem1; // 完成位置互换

                        $tem2 = $reduction_info['free_shipping'][$i]; // 这里临时变量，存贮$i的值
                        $reduction_info['free_shipping'][$i] = $reduction_info['free_shipping'][$j]; // 第一次更换位置
                        $reduction_info['free_shipping'][$j] = $tem2; // 完成位置互换
                    }
                }
            }
            $price = $res['order']['payment_money'] - $res['order']['express_price'];
            $reduction_achieve = 0;
            $free_shipping = false;
            for ($i = 0; $i < count($reduction_info['reduction_achieve']); $i++) {
                if ($price >= $reduction_info['reduction_achieve'][$i]) {
                    $reduction_achieve = $reduction_info['reduction_achieve'][$i];
                    $free_shipping = $reduction_info['free_shipping'][$i];
                }
            }
            $res['order']['reduction_achieve'] = $reduction_achieve;
            //var_dump($free_shipping);die();
            if ($free_shipping == true) {
                $res['order']['total_price']=$res['order']['payment_money'] - $res['order']['express_price'];
                $res['order']['express_price'] = 0;
                $res['order']['payment_money'] = $price - $reduction_achieve;
            } else {
                $res['order']['payment_money'] = $price - $reduction_achieve + $res['order']['express_price'];
            }

        }

        $res['order']['estimated_service_time'] = $data['estimated_service_time'];
        //团长信息//门店信息
        if ($data['supplier_id'] == 0) {
            if ($data['leader_id'] != 0) {
                $leader = $this->leader($data);
                if ($leader['status'] == 200) {
                    $res['order']['leader_uid'] = $leader['data'];
                    $res['order']['leader_self_uid'] = $data['leader_id'];
                }
            }
        } else {
            $leaderModel = new LeaderModel();
            $leaderData = $leaderModel->do_one(['supplier_id' => $data['supplier_id']]);
            if ($leaderData['status'] == 200) {
                $res['order']['leader_uid'] = $leaderData['data']['supplier_id'];
                $res['order']['leader_self_uid'] = $leaderData['data']['supplier_id'];
            }

        }
        $appaccessModel = new AppAccessModel();
        $merchant = $appaccessModel->find(['merchant_id'=>yii::$app->session['merchant_id'],'`key`'=>yii::$app->session['key']]);
        if($merchant['status']!=200){
            return result(500,'服务器错误');
        }
        if($res['order']['payment_money']<=($merchant['data']['starting_price']-0.01)){
            $aaa = $merchant['data']['starting_price']-$res['order']['payment_money'];
            return result(500,"店铺最低{$merchant['data']['starting_price']}元起订，还差{$aaa}元");
        }
        $bool = $this->order($res, $data);
        return $bool;
    }


    public function order($order, $data)
    {

        //var_dump($order);die();
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $voucherModel = new VoucherModel();
            $orderGroupModel = new OrderModel();
            $orderModel = new SubOrderModel();
            if ($data['voucher_id'] != 0) {
                $voucherModel->update(['id' => $data['voucher_id'], 'status' => 0]);
            }
            //提交订单商户信息有误
            $order['order']['transaction_order_sn'] = $data['transaction_order_sn'];
            $orderGroupModel->add($order['order']);
            $systemPayModel = new PayModel();
            $systemPayData = array(
                'order_id' => $order['order']['order_sn'],
                'user_id' => yii::$app->session['user_id'],
                'merchant_id' => yii::$app->session['merchant_id'],
                'remain_price' => $order['order']['payment_money'],
                'type' => 3,
                'total_price' => $order['order']['total_price'],
                'status' => 2,
            );

            $systemPayModel->add($systemPayData);
            for ($i = 0; $i < count($order['subOrder']); $i++) {
                $orderModel->add($order['subOrder'][$i]);
            }

//            $cartModel = new CartModel();
            //删除购物车商品
//            for ($i = 0; $i < count($params['goods']); $i++) {
//                if (isset($params['goods'][$i]['id'])) {
//                    $cartModel->delete(['id' => $params['goods'][$i]['id']]);
//                }
//            }


            $comboAccessModel = new \app\models\merchant\system\MerchantComboAccessModel();
            $comboAccessModel->do_update(['id' => $data['combo_id']], ['order_remain_number' => $data['combo_number'] - 1]);

            $cartModel = new CartModel();
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行

            return result(200, '请求成功', ['order_sn' => $data['transaction_order_sn'], 'group_id' => 0, 'group_number' => 0]);
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "订单提交失败！");
        }
    }

    public function voucher($id, $total)
    {
        $voucherModel = new VoucherModel();
        $voucherParams['user_id'] = yii::$app->session['user_id'];
        $voucherParams['merchant_id'] = yii::$app->session['merchant_id'];
        $voucherData['id'] = $id;
        $voucherData = $voucherModel->find($voucherData);
        if ($voucherData['status'] != 200) {
            return result(500, "该优惠券已使用，或已失效！");
        }

        if ($voucherData['data']['full_price'] == 0 || $voucherData['data']['full_price'] <= $total) {
            $payment_money = $total - $voucherData['data']['price'];
        } else {
            return result(500, "该优惠券未达到使用标准！");
        }
        return result(200, "该优惠券达到使用标准！", $payment_money);

    }

    public function vip($payment_money)
    {
        $userModel = new UserModel();
        $orderGroupModel = new OrderModel();
        $where['id'] = yii::$app->session['user_id'];
        $userInfo = $userModel->find($where);
        if ($userInfo['status'] != 200) {
            return result(500, '未找到此用户');
        }
        $discount_ratio = 1;
        if ($userInfo['data']['is_vip'] == 1 && $userInfo['data']['vip_validity_time'] >= time()) {
            //检测用户是否有开启的vip会员卡，防止商户禁用
            $vipAccessModel = new VipAccessModel();
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $user_id = yii::$app->session['user_id'];
            $where_ = "sva.`key` = '{$key}' 
            AND sva.merchant_id = {$merchant_id} 
            AND sva.user_id = {$user_id}
            AND sva.`status`=1
            AND sv.`status`=1";
            $sql = "SELECT sva.*,sv.`status` as sv_status FROM shop_vip_access as sva
                          LEFT JOIN shop_vip as sv ON sva.vip_id = sv.id WHERE  " . $where_;
            $list = $orderGroupModel->querySql($sql);
            if ($list) {
                $vipConfigModel = new VipConfigModel();
                $whereConfig['key'] = yii::$app->session['key'];
                $whereConfig['merchant_id'] = yii::$app->session['merchant_id'];
                $whereConfig['status'] = 1;
                $info = $vipConfigModel->one($whereConfig);
                $payment_money = bcmul($payment_money, $info['data']['discount_ratio'], 2); // 计算优惠打折
                $discount_ratio = $info['data']['discount_ratio'];
            }
        }
        return result(200, "该优惠券达到使用标准！", $payment_money);
    }

    public function leader($data)
    {
        $leaderModel = new \app\models\tuan\UserModel;
        $leaderData = $leaderModel->do_one(['uid' => yii::$app->session['user_id']]);
        if ($leaderData['status'] == 200) {
            return result(200, "请求成功", $leaderData['data']['leader_uid']);
        } else if ($leaderData['status'] == 204) {
            $tuanUser = array(
                'key' => yii::$app->session['key'],
                'merchant_id' => yii::$app->session['merchant_id'],
                'uid' => yii::$app->session['user_id'],
                'is_verify' => 0,
                'leader_uid' => $data['leader_id'],
                'status' => 1,
            );
            $tuanUserModel = new \app\models\tuan\UserModel();
            $tuanUserModel->do_add($tuanUser);
            return result(200, "请求成功！", $data['leader_id']);
        } else {
            return $leaderData;
        }
    }


    public function goods($goods, $data)
    {
        $user_id = yii::$app->session['user_id'];
        $key = yii::$app->session['key'];
        $merchant_id = yii::$app->session['merchant_id'];
        $stockModel = new StockModel();
        $goodModel = new GoodsModel();
        $orderGroupModel = new OrderModel();
        $total_price = 0;
        $service_goods_status = 0;
        $address = "";
        $name = "";
        $phone = "";
        $number = 0;
        $is_bargain = 0;
        $goodsname = "";
        $weight = 0;
        do {
            $order_sn = order_sn();
            $orderFindData['order_sn'] = $order_sn;
            $rs = $orderGroupModel->find($orderFindData);
        } while ($rs['status'] == 200);

        for ($i = 0; $i < count($goods); $i++) {
            $stockData = $stockModel->find(['id' => $goods[$i]['stock_id']]);
            $goodData = $goodModel->find(['id' => $goods[$i]['goods_id']]);
            if ($goodData['status'] != 200 && $stockData['status'] != 200) {
                return result(500, "找不到该商品或商品已下架");
            }

            if ($goodData['data']['is_recruits'] == 1) {
                $sql = "select count(id)as num from shop_order_group where (status >2 or status =1) and  user_id = {$user_id}";
                $is_recruits = $orderGroupModel->querySql($sql);
                if ($is_recruits[0]['num'] !== 0) {
                    return result(500, "您不是新人，无法购买新人专享商品");
                }
            }
            if (count($goods) == 1 && $goodData['data']['type'] == 3 && $goodData['data']['service_goods_is_ship'] == 1) {
                $service_goods_status = 1;
            }
            if ($goodData['data']['is_limit'] == 1 && $goodData['data']['limit_number'] > 0) { // 检测此商品被购买了多少次
                $sql = "SELECT sum(so.number) as total FROM shop_order_group as sog
                          LEFT JOIN shop_order as so ON sog.order_sn = so.order_group_sn WHERE  so.goods_id = {$goods[$i]['goods_id']} and sog.`status` in  (0,1,3,5,6,7) and sog.user_id = {$user_id} ";
                $total = $orderGroupModel->querySql($sql);
                if ((int)$total[0]['total'] >= (int)$goodData['data']['limit_number']) {
                    return result(500, "此商品已限量了！");
                }
            }
            $time = time();
            $sql = "SELECT * FROM `shop_flash_sale_group` where FIND_IN_SET({$goods[$i]['goods_id']},goods_ids) and start_time <={$time} and end_time >={$time} and `key` = '{$key}' and merchant_id = {$merchant_id} and delete_time is null;";
            $res = yii::$app->db->createCommand($sql)->queryAll();

            if (count($res) == 0) {
                if ($stockData['data']['number'] == 0) {
                    return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}已售罄!");
                } else if ($stockData['data']['number'] < $goods[$i]['number']) {
                    return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}购买数量超出库存!");
                }
                $subGoods[$i]['price'] = $stockData['data']['price'];
                $subGoods[$i]['is_flash_sale'] = 0;
            } else {
                $sql = "SELECT * FROM `shop_flash_sale` where goods_id = {$goods[$i]['goods_id']} and delete_time is not null ";
                $res = yii::$app->db->createCommand($sql)->queryAll();
                $property = explode("-", $res[0]['property']);
                for ($k = 0; $k < count($property); $k++) {
                    $a = json_decode($property[$k], true);
                    if ($stockData['data']['id'] == $a['stock_id']) {
                        if ($a['stocks'] == 0) {
                            return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}已售罄!");
                        } else if ($a['stocks'] < $goods[$i]['number']) {
                            return result(500, "该商品{$goodData['data']['name']}-{$stockData['data']['property1_name']}-{$stockData['data']['property1_name']}购买数量超出库存!");
                        }
                        $subGoods[$i]['price'] = $a['flash_price'];
                        $stockData['data']['price'] = $a['flash_price'];
                    }

                }
                $subGoods[$i]['is_flash_sale'] = 1;
            }

            $is_bargain = 0;
            //砍价
            if ($data['bargin_id'] != 0) {
                if ($goodData['data']['is_bargain'] == 1) {
                    $bargainModel = new ShopBargainInfoModel();
                    $bargins = $bargainModel->do_one(['id' => $data['bargin_id'], 'goods_id' => $goodData['data']['id'], 'promoter_user_id' => yii::$app->session['user_id']]);
                    $barginInfo = $bargainModel->do_one(['orderby' => 'id desc', 'goods_id' => $goodData['data']['id'], 'promoter_user_id' => yii::$app->session['user_id'], 'promoter_sn' => $bargins['data']['promoter_sn']]);
                    //var_dump($barginInfo);die();
                    $subGoods[$i]['price'] = $barginInfo['data']['goods_price'];
                    $stockData['data']['price'] = $barginInfo['data']['goods_price'];
                    $is_bargain = 1;
                }
            }

            if ($i == 0) {
                $total_price = $stockData['data']['price'] * $goods[$i]['number'];
                $goodsname = $goodData['data']['name'];
            } else {
                $total_price = $total_price + $stockData['data']['price'] * $goods[$i]['number'];
                $goodsname = $goodsname . "," . $goodData['data']['name'];
            }

            $number = $number + $goods[$i]['number'];
            //子订单数据
            $supplier_id = $goodData['data']['supplier_id'];
            $subGoods[$i]['`key`'] = yii::$app->session['key'];
            $subGoods[$i]['merchant_id'] = yii::$app->session['merchant_id'];
            $subGoods[$i]['user_id'] = yii::$app->session['user_id'];
            $subGoods[$i]['goods_id'] = $goodData['data']['id'];
            $subGoods[$i]['order_group_sn'] = $order_sn;
            $subGoods[$i]['stock_id'] = $stockData['data']['id'];
            $subGoods[$i]['pic_url'] = $stockData['data']['pic_url'];
            $subGoods[$i]['name'] = $goodData['data']['name'];
            $subGoods[$i]['number'] = $goods[$i]['number'];
            $subGoods[$i]['price'] = $stockData['data']['price'];
            $weight = $weight + $stockData['data']['weight'] * $goods[$i]['number'];
            $subGoods[$i]['payment_money'] = $stockData['data']['price'] * $goods[$i]['number'];
            $subGoods[$i]['total_price'] = $stockData['data']['price'] * $goods[$i]['number'];
            $subGoods[$i]['property1_name'] = isset($params['goods'][$i]['property1_name']) ? $goods[$i]['property1_name'] : "";
            $subGoods[$i]['property2_name'] = isset($params['goods'][$i]['property2_name']) ? $goods[$i]['property2_name'] : "";
        }

        if ($data['user_contact_id'] == 0) {
            //收货地址
            $phone = $data['phone'];
            $name = $data['name'];
        }
        $express_price = 0.00;
        if ($data['type'] == 0) {
            //收货地址
            $contactModel = new ContactModel();
            if (!isset($data['user_contact_id'])) {
                return result(500, '请填写收货地址');
            }
            $contactParams['id'] = $data['user_contact_id'];
            $contactParams['user_id'] = yii::$app->session['user_id'];
            $contactData = $contactModel->find($contactParams);
            if ($contactData['status'] != 200) {
                return result(500, '未找到该收货地址');
            }
            $user_contact_id = $contactData['data']['id'];

            $address = $contactData['data']['loction_address']. $contactData['data']['loction_name']. "-" .$contactData['data']['address'];
            $phone = $contactData['data']['phone'];
            $name = $contactData['data']['name'];


            //快递费
            // var_dump($contactData['data']['id']);die();
            $express = $this->express($number, $contactData['data']['id'], $weight);
            //  var_dump($express);die();
            if ($express['status'] != 200) {
                return $express;
            } else {
                $express_price = $express['data'];
            }
        } else if ($data['type'] == 1) { // 自提
            $express_price = 0;
        } else if ($data['type'] == 2) { // 团长配送
            $express_price = 0;
            $tuanLeaderModel = new \app\models\tuan\LeaderModel();
            if ($data['supplier_id'] == 0) {
                $lerder = $tuanLeaderModel->do_one(['uid' => $data['leader_id']]);
                if ($lerder['data']['is_tuan_express'] == 0) {
                    return result(500, "该团在未开启配送");
                }
                $express_price = $lerder['data']['tuan_express_fee'];
            } else {
                $lerder = $tuanLeaderModel->do_one(['supplier_id' => $data['supplier_id']]);
                if ($lerder['data']['is_tuan_express'] == 0) {
                    return result(500, "该门店未开启配送");
                }
                $express_price = $lerder['data']['tuan_express_fee'];
            }
        }


        $order = array(
            '`key`' => yii::$app->session['key'],
            'merchant_id' => yii::$app->session['merchant_id'],
            'user_id' => yii::$app->session['user_id'],
            'goodsname' => $goodsname,
            'order_sn' => $order_sn,
            'user_contact_id' => $data['user_contact_id'],
            'address' => $address,
            'phone' => $phone,
            'name' => $name,
            'total_price' => $total_price + $express_price,
            'payment_money' => $total_price + $express_price,
            'voucher_id' => isset($data['voucher_id']) ? $data['voucher_id'] : 0,
            'express_price' => $express_price,
            'express_type' => $data['type'],
            'after_sale' => -1,
            'status' => 0,
            'remark' => isset($data['remark']) ? $data['remark'] : "",
            'supplier_id' => $data['supplier_id'],
            'partner_id' => $data['partner_id'] ?? 0,
            'create_time' => time(),
            'service_goods_status' => $service_goods_status,
            'estimated_service_time' => isset($goods['estimated_service_time']) ? $goods['estimated_service_time'] : "",
            'is_assemble' => 0,
            'is_tuan' => $data['is_tuan'],
            'is_bargain' => $is_bargain,
        );

        unset($data['partner_id']);
        $res['order'] = $order;
        $res['subOrder'] = $subGoods;
        return result(200, "请求成功", $res);
    }


    //type 寄送类型  ￥number 数量  id 收货地址 $weight 重量
    public function express($number, $id, $weight)
    {
        $model = new ShopExpressTemplateModel();
        $temp = $model->find(['status' => 1, 'merchant_id' => yii::$app->session['merchant_id'], '`key`' => yii::$app->session['key']]);
        if ($temp['status'] != 200) {
            return $temp;
        }
        $type = $temp['data']['type'];
        $templateModel = new ShopExpressTemplateDetailsModel();
        //寄件 寄重
        if ($type == 1) {

            $model = new ContactModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['user_id'] = yii::$app->session['user_id'];
            $tempModel = new ShopExpressTemplateModel();
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['`key`'] = yii::$app->session['key'];
            $data['status'] = 1;
            $temp = $tempModel->find($data);
            if ($temp['status'] != 200) {
                return result(500, "快递费获取失败");
            }
            $address = $model->find($params);
            $price = 0;
            $kdmb = new ShopExpressTemplateDetailsModel();

            unset($params['id']);
            $data['searchName'] = $address['data']['province'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['`key`'] = yii::$app->session['key'];
            $data['shop_express_template_id'] = $temp['data']['id'];
            $data['status'] = 1;
            if ($address['status'] == 200) {
                $data['searchName'] = $address['data']['province'];
                $kdf = $kdmb->find($data);
            } else {
                $params['searchName'] = "全国统一运费";
                $kdf = $kdmb->find($data);
            }
            if ($kdf['status'] != 200) {
                $data['searchName'] = "全国统一运费";
                $kdf = $kdmb->find($data);
                $price = $kdf['data']['expand_price'];
            }
            $price = $kdf['data']['first_price'] + (($number - 1) * $kdf['data']['expand_price']);
            $price = $price == 0 ? "0" : $price;
            return result(200, "请求成功", $price);
        } else if ($type == 2) {
            $model = new ContactModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['user_id'] = yii::$app->session['user_id'];
            $tempModel = new ShopExpressTemplateModel();
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['`key`'] = yii::$app->session['key'];
            $data['status'] = 1;
            $temp = $tempModel->find($data);
            if ($temp['status'] != 200) {
                return result(500, "快递费获取失败");
            }
            $address = $model->find($params);
            $price = 0;
            $kdmb = new ShopExpressTemplateDetailsModel();

            unset($params['id']);
            $data['searchName'] = $address['data']['province'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['`key`'] = yii::$app->session['key'];
            $data['shop_express_template_id'] = $temp['data']['id'];
            $data['status'] = 1;
            if ($address['status'] == 200) {
                $data['searchName'] = $address['data']['province'];
                $kdf = $kdmb->find($data);
            } else {
                $params['searchName'] = "全国统一运费";
                $kdf = $kdmb->find($data);
            }
            if ($kdf['status'] != 200) {
                $data['searchName'] = "全国统一运费";
                $kdf = $kdmb->find($data);
            }
            if ($weight <= $kdf['data']['first_num']) {
                $price = $kdf['data']['first_price'];
            } else {
                $num1 = ($weight - $kdf['data']['first_num']) / $kdf['data']['expand_num'];
                $num2 = ($weight - $kdf['data']['first_num']) % $kdf['data']['expand_num'];
                if ($num2 != 0) {
                    $num1 = $num1 + 1;
                }
                $price = $kdf['data']['first_price'] + ($num1 * $kdf['data']['expand_price']);
            }
            return result(200, "请求成功", $price);
        } else if ($type == 3) {
            //寄距离
            $contactModel = new ContactModel();
            $params['id'] = $id;
            $address = $contactModel->find($params);
            if ($address['status'] != 200) {
                return $address;
            }
            $appAccessModel = new AppAccessModel();
            $merchan_info = $appAccessModel->find(['`key`' => yii::$app->session['key']]);
            if ($merchan_info['status'] != 200) {
                return $merchan_info;
            }
            if ($address['data']['longitude'] == "" || $address['data']['latitude'] == "") {
                return result(500, "请求失败,坐标获取失败 无法计算距离");
            }
            if ($merchan_info['data']['coordinate'] == "") {
                return result(500, "请求失败,坐标获取失败 无法计算距离");
            }
            $origin = $address['data']['longitude'] . "," . $address['data']['latitude'];//出发地
            $destination = $merchan_info['data']['coordinate'];//目的地
            $juli = 0;
            $yunfei = 0;
            $url = "https://restapi.amap.com/v3/direction/walking?origin={$origin}&destination={$destination}&key=bc55956766e813d3deb1f95e45e97d73&output=json";
            $result = json_decode(curlGet($url), true);

            if ($result['status'] == 1) {
                $juli = $result['route']['paths']['0']['distance'] / 1000;
            } else {
                return result(500, "请求失败，距离计算错误");
            }
            $express = $templateModel->find(['shop_express_template_id' => $temp['data']['id']]);

            if ($express['status'] != 200) {
                return $express;
            }
            $fw = json_decode($express['data']['distance'], true);
            //{"start_number":["0","4"],"end_number":["3","6"],"freight":["6","11"]}
            for ($i = 0; $i < count($fw['start_number']); $i++) {
                if ($fw['start_number'][$i] < $juli && $fw['end_number'][$i] > $juli) {
                    $yunfei = $fw['freight'][$i];
                }
            }
            return result(200, "请求成功", $yunfei);
        }


    }

    /**
     * @param $log_content
     */
    private function logger($log_content)
    {
        if (isset($_SERVER['HTTP_APPNAME'])) {   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        } else if ($_SERVER['REMOTE_ADDR'] != "127.0.0.1") { //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if (file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)) {
                unlink($log_filename);
            }
            file_put_contents($log_filename, date('Y-m-d H:i:s') . " " . $log_content . "\r\n", FILE_APPEND);
        }
    }

}
