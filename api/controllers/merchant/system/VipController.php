<?php

namespace app\controllers\merchant\system;


use yii;
use yii\web\MerchantController;
use yii\base\Exception;
use app\models\system\SystemVipUserModel;
use app\models\merchant\user\MerchantModel;
use app\models\system\VipModel;
use alipay\Alipay;
use WxPay\Wechat;
use app\models\merchant\pay\PayModel;
use alipay\AlipayTradeService;

require_once Yii::getAlias('@vendor/alipay/pagepay/Alipay.php');
require_once Yii::getAlias('@vendor/alipay/pagepay/service/AlipayTradeService.php');
require_once yii::getAlias('@vendor/wxpay/Wechat.php');

class VipController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['alipay', 'wxpay', 'query', 'pay'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new VipModel();
            $array = $model->do_select($params);
            $array['merchant_id'] = yii::$app->session['uid'];
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemVipUserModel();
            $array = $model->do_one(['merchant_id' => yii::$app->session['uid']]);

            $payModel = new PayModel();
            $res = $payModel->findall(['merchant_id' => yii::$app->session['uid'], 'remark' => 'vip购买充值']);
            if ($res['status'] == 200) {
                $id = explode("_", $res['data'][0]['order_id']);
                $vipModel = new VipModel();
                $vipinfo = $vipModel->do_one(['id' => $id[1]]);
                $array['vipinfo'] = $vipinfo['data'];
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemVipUserModel();
            $array = $model->do_one(['merchant_id' => yii::$app->session['uid']]);
            $must = ['province_code', 'city_code', 'area_code', 'addr', 'company_name', 'telephone', 'qq', 'email'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['status'] = 0;
            if ($array['status'] == 200) {
                if ($array['data']['status'] == 0 || $array['data']['status'] == 2) {
                    $array = $model->do_update(['merchant_id' => yii::$app->session['uid']], $params);
                }
            }
            if ($array['status'] == 204) {
                $params['merchant_id'] = yii::$app->session['uid'];
                $array = $model->do_add($params);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionPay() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new VipModel();
            $vip = $model->do_one(['id' => $params['id']]);
            if ($vip['status'] != 200) {
                return $vip;
            }

            $payModel = new PayModel();
            if ($params['type'] == "ali") {
                $type = 1;
            } else if ($params['type'] == "wechat") {
                $type = 2;
            } else {
                $type = 0;
            }
            $order = "vip_" . $params['id'] . "_" . time() . rand(1000, 9999);
            $array = array(
                'order_id' => $order,
                //  'user_id'=>  yii::$app->session['uid'],
                'merchant_id' => $params['merchant_id'],
                'remain_price' => $vip['data']['money'],
                'type' => $type,
                'total_price' => $vip['data']['money'],
                'status' => 2,
                'remark' => 'vip购买充值'
            );
            $res = $payModel->add($array);
            if ($res['status'] != 200) {
                return $res;
            }
            if ($params['type'] == "ali") {
                $alipay = new Alipay();
                $data['WIDout_trade_no'] = $order;
                $data['WIDsubject'] = $vip['data']['name'];

                //付款金额，必填
                // $data['WIDtotal_amount'] = $payinfo['data']['remain_price'];
                $data['WIDtotal_amount'] = 0.01;
                //商品描述，可空
                $data['WIDbody'] = $vip['data']['detail_info'];
                $config = yii::$app->params['ali_config'];
                $config['notify_url'] = "https://api2.juanpao.com/merchant/vip/alipay";
                $config['return_url'] = "http://web2.juanpao.com/adminMerchant/aliReturnVip.html";
                $alipay->pagepay($data, $config);
                die();
            }
            if ($params['type'] == "wechat") {
                $wx = new Wechat();
                $data['trade_no'] = $order;
                $data['name'] = $vip['data']['name'];
                $data['money'] = 1;
                $data['goos_tag'] = $vip['data']['detail_info'];
                $data['notify_url'] = "http://api.juanpao.com/merchant/vip/wxpay";
                $config = json_decode(json_encode(yii::$app->params['wx_config']), false);
                $result = $wx->wxPayUnifiedOrder($data, $config);
                $array = [
                    'status' => 200,
                    'message' => '请求成功',
                    'out_trade_no' => $params['id'],
                    'data' => 'http://api.juanpao.com/pay/wechat/qrcode?data=' . $result,
                ];
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    //阿里回调
    public function actionAlipay() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->post(); //获取地址栏参数
            $config = yii::$app->params['ali_config'];
            $config['notify_url'] = "https://api2.juanpao.com/merchant/vip/alipay";
            $config['return_url'] = "http://web2.juanpao.com/adminMerchant/aliReturnVip.html";
            $alipaySevice = new AlipayTradeService($config);

            $params['sign'] = urldecode($params['sign']);
            $params['timestamp'] = urldecode($params['timestamp']);
            $result = $alipaySevice->check($params);
            /* 实际验证过程建议商户添加以下校验。
              1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
              2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
              3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
              4、验证app_id是否为该商户本身。
             */
            if ($result) {//验证成功
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                //请在这里加上商户的业务逻辑程序代码
                //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
                //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表
                //商户订单号
                // $out_trade_no = $params['out_trade_no'];
                //支付宝交易号
                // $trade_no = $params['trade_no'];
                $rs = true;
                try {
                    $payModel = new PayModel();
                    $res = $payModel->find(['order_id' => $params['out_trade_no']]);

                    $data['status'] = 1;
                    $data['pay_time'] = time();
                    $data['transaction_id'] = $params['trade_no'];
                    $data['order_id'] = $params['out_trade_no'];
                    $payModel->update($data);

                    $systemVipUserModel = new SystemVipUserModel();
                    $systemVipUserModel->do_update(['merchant_id' => $res['data']['merchant_id']], ['status' => 3]);

                    $userModel = new MerchantModel();
                    $userModel->update(['id' => $res['data']['merchant_id'], 'balance' => $res['data']['total_price']]);
                } catch (Exception $e) {
                    $rs = false;
                }

                if ($rs == false) {
                    return result(500, "回调验证错误，支付失败");
                }
                if ($rs == true) {
                    return result(200, "支付成功");
                }
                //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            } else {
                return result(500, "支付失败");
            }
        }
    }

    //微信回调
    public function actionWxpay() {
        $xml = file_get_contents("php://input");

        $wxPatNotify = new \WxPayNotify();
        $wxPatNotify->Handle(false);
        $returnValues = $wxPatNotify->GetValues();
        $result = $wxPatNotify->FromXml($xml);

        if (!empty($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            //商户逻辑处理，如订单状态更新为已支付
            $rs = true;
            try {
                $payModel = new PayModel();
                $res = $payModel->find(['order_id' => $result['out_trade_no']]);

                $data['status'] = 1;
                $data['pay_time'] = time();
                $data['transaction_id'] = $result['transaction_id'];
                $data['order_id'] = $result['out_trade_no'];
                $payModel->update($data);

                $systemVipModel = new VipModel();
                $systemVipModel->do_update(['merchant_id' => $res['data']['merchant_id']], ['status' => 3]);

                $userModel = new MerchantModel();
                $userModel->update(['id' => $res['data']['merchant_id'], 'balance' => $res['data']['total_price']]);
            } catch (Exception $e) {
                $rs = false;
            }
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
     * 订单查询
     * @throws \Exception
     */
    public function actionQuery($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数
        //判断必填
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
            $result = $wx->orderQuery($id, $config);
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
        $array = result(200, '请求成功', $arr);
        return $array;
    }



}
