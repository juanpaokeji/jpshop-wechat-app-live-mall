<?php

namespace app\controllers\merchant\system;

use yii;
use yii\web\MerchantController;
use app\models\merchant\system\MerchantComboModel;
use app\models\merchant\pay\PayModel;
use alipay\Alipay;
use WxPay\Wechat;
use alipay\AlipayTradeService;

require_once Yii::getAlias('@vendor/alipay/pagepay/Alipay.php');
require_once Yii::getAlias('@vendor/alipay/pagepay/service/AlipayTradeService.php');
require_once yii::getAlias('@vendor/wxpay/Wechat.php');

class ComboController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['sms', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['alipay', 'wxpay', 'query', 'pay'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionOne() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数      
            $model = new \app\models\merchant\system\MerchantComboAccessModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $key = $params['key'];
            unset($params['key']);
            $params['system_merchant_combo_access.key'] = $key;
            $params['system_merchant_combo_access.status'] = 1;
            $params['system_merchant_combo_access.type'] = 2;
            $params['field'] = "system_merchant_combo.name as order_version";
            $params['join'][] = ['inner join', 'system_merchant_combo', 'system_merchant_combo.id = system_merchant_combo_access.combo_id'];
            $params['>'] = ['system_merchant_combo_access.validity_time', time()];
            $array = $model->do_select($params);
     
            if ($array['status'] == 200) {
                return result(200, '请求成功', $array['data'][0]);
            }
            if ($array['status'] == 204) {
                return result(200, '请求成功', array('order_version' => "免费版"));
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数      
            $model = new MerchantComboModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $key = $params['key'];
            unset($params['key']);
            $params['status'] = 1;
            $array = $model->do_select($params);

            $sql = "select sum(order_remain_number)as order_number ,sum(sms_remain_number) as sms_number from system_merchant_combo_access where merchant_id = " . yii::$app->session['uid'] . "  and `key` = '{$key}' and validity_time >=" . time();

            $res = Yii::$app->db->createCommand($sql)->queryOne();

            $array['order_count'] = $res['order_number'] == null ? 0 : $res['order_number'];
            $array['sms_count'] = $res['sms_number'] == null ? 0 : $res['sms_number'];
            $array['merchant_id'] = yii::$app->session['uid'];


            $accessModel = new \app\models\merchant\system\MerchantComboAccessModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $where['system_merchant_combo_access.key'] = $key;
            $where['system_merchant_combo_access.merchant_id'] = yii::$app->session['uid'];
            $where['system_merchant_combo_access.status'] = 1;
            $where['field'] = "system_merchant_combo_access.*,system_merchant_combo.name as combo_name ";
            $where['join'][] = ['inner join', 'system_merchant_combo', 'system_merchant_combo.id = system_merchant_combo_access.combo_id'];
            $where['system_merchant_combo.type'] = 2;
            $data = $accessModel->do_select($where);
            if ($data['status'] == 200) {
                $data['data'][0]['format_validity_time'] = date('Y-m-d', $data['data'][0]['validity_time']);
                $array['combo'] = $data['data'][0];
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数      
            $model = new \app\models\merchant\system\MerchantComboAccessModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $params['system_merchant_combo_access.key'] = $params['key'];
            unset($params['key']);
            $params['system_merchant_combo_access.merchant_id'] = yii::$app->session['uid'];
            $params['system_merchant_combo_access.status'] = 1;
            $params['field'] = "system_merchant_combo_access.*,system_merchant_combo.name as combo_name ";
            $params['join'][] = ['inner join', 'system_merchant_combo', 'system_merchant_combo.id = system_merchant_combo_access.combo_id'];
            $array = $model->do_select($params);
            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['format_validity_time'] = date('Y-m-d', $array['data'][$i]['validity_time']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionSingle($id) {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
//            $model = new MerchantComboModel();
//            $params['id'] = $id;
//            $array = $model->do_one($params);
//
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    public function actionAdd() {
//        if (yii::$app->request->isPost) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new MerchantComboModel();
//            $must = ['name', 'pic_url', 'number', 'money', 'type', 'detail_info', 'status'];
//            //设置类目 参数
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return $rs;
//            }
//            if ($params['pic_url'] != "") {
//                $base = new Base64Model();
//                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/decoration");
//                $cos = new CosModel();
//                $cosRes = $cos->putObject($params['pic_url']);
//                if ($cosRes['status'] == '200') {
//                    $url = $cosRes['data'];
//                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
//                } else {
//                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
//                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                }
//                $params['pic_url'] = $url;
//            }
//            //  $params['count'] = (int) $params['count'];
//
//            $array = $model->do_add($params);
//
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    public function actionUpdate($id) {
//        if (yii::$app->request->isPut) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new MerchantComboModel();
//            $where['id'] = $id;
//            if (isset($params['pic_url'])) {
//                if ($params['pic_url'] != "") {
//                    $base = new Base64Model();
//                    $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/vip");
//                    $cos = new CosModel();
//                    $cosRes = $cos->putObject($params['pic_url']);
//                    if ($cosRes['status'] == '200') {
//                        $url = $cosRes['data'];
//                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
//                    } else {
//                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
//                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                    }
//                    $params['pic_url'] = $url;
//                } else {
//                    unset($params['pic_url']);
//                }
//            }
//            $array = $model->do_update($where, $params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    public function actionDelete($id) {
//        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new MerchantComboModel();
//            $params['id'] = $id;
//            $array = $model->do_delete($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    public function actionPay($id) {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new MerchantComboModel();
            $params['id'] = $id;
            $combo = $model->do_one(['id' => $params['id']]);
            if ($combo['status'] != 200) {
                return $combo;
            }
            $comboAccess = new \app\models\merchant\system\MerchantComboAccessModel();
            $res = $comboAccess->do_select(['combo_id' => $params['id']]);
            if ($res['status'] == 200) {
                if ($combo['data']['number'] <= count($res['data']) && $combo['data']['number'] != 0) {
                    return result(500, '您已超过改套餐的购买次数');
                }
            }
            if ($res['status'] == 500) {
                return $res;
            }


            $order = "combo_" . date("YmdHis", time()) . rand(1000, 9999);
            $comboData = array(
                'merchant_id' => $params['merchant_id'],
                'key' => $params['key'],
                'order_sn' => $order,
                'combo_id' => $params['id'],
                'sms_number' => $combo['data']['sms_number'],
                'order_number' => $combo['data']['order_number'],
                'sms_remain_number' => $combo['data']['sms_number'],
                'order_remain_number' => $combo['data']['order_number'],
                'validity_time' => $combo['data']['validity_time'],
                'type' => $combo['data']['type'],
                'status' => 0,
            );
            $res = $comboAccess->do_add($comboData);
            if ($res['status'] != 200) {
                return $res;
            }
            if ($params['type'] == "ali") {
                $alipay = new Alipay();
                $data['WIDout_trade_no'] = $order;
                $data['WIDsubject'] = $combo['data']['name'];
                //付款金额，必填
                $data['WIDtotal_amount'] = $combo['data']['money'];
                //   $data['WIDtotal_amount'] = 0.01;
                //商品描述，可空
                $data['WIDbody'] = $combo['data']['detail_info'];
                $config = yii::$app->params['ali_config'];
                $config['notify_url'] = "https://api.juanpao.com/merchant/system/combo/alipay";
                $config['return_url'] = "http://api.juanpao.com/adminMerchant/aliReturnCombo.html";
                $alipay->pagepay($data, $config);
                die();
            }
            if ($params['type'] == "wechat") {
                $wx = new Wechat();
                $data['trade_no'] = $order;
                $data['name'] = $combo['data']['name'];
                $data['attach'] = "";
                $data['money'] = $combo['data']['money'] * 100;
                $data['goos_tag'] = $combo['data']['detail_info'];
                $data['notify_url'] = "http://api.juanpao.com/merchant/system/combo/wxpay";
                $config = json_decode(json_encode(yii::$app->params['wx_config']), false);
                $result = $wx->wxPayUnifiedOrder($data, $config);
                $array = [
                    'status' => 200,
                    'message' => '请求成功',
                    'out_trade_no' => $order,
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
            $config['notify_url'] = "https://api2.juanpao.com/merchant/system/combo/alipay";
            $config['return_url'] = "http://web2.juanpao.com/adminMerchant/aliReturnCombo.html";
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
                    $comboAccess = new \app\models\merchant\system\MerchantComboAccessModel();
                    $res = $comboAccess->do_one(['order_sn' => $params['out_trade_no']]);
                    if ($res['data']['status'] == 0) {
                        $data['status'] = 1;
                        $data['validity_time'] = strtotime(date('Y-m-d', strtotime("+ {$res['data']['validity_time']}month")));
                        $data['remarks'] = "支付宝";
                        $comboAccess->do_update(['order_sn' => $params['out_trade_no']], $data);
                    }
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
        //  $this->logger(json_encode($result));
//        $result = json_decode(' {"appid":"wx52095822757a8bf0","bank_type":"CFT","cash_fee":"1","fee_type":"CNY","is_subscribe":"Y","mch_id":"1496441282","nonce_str":"jt48ocxrv6ib4r9ur35djjw9z4tuihye","openid":"oIgQV0sQIKph01YUQAif6d-5OKTk","out_trade_no":"combo_201905101027283491","result_code":"SUCCESS","return_code":"SUCCESS","sign":"CD12807193FD049FCF11A035219AF7A9","time_end":"20190510102740","total_fee":"1","trade_type":"NATIVE","transaction_id":"4200000311201905107350070028"}', true);
        if (!empty($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            //商户逻辑处理，如订单状态更新为已支付
            $rs = true;
            try {

                $comboAccess = new \app\models\merchant\system\MerchantComboAccessModel();
                $res = $comboAccess->do_one(['order_sn' => $result['out_trade_no']]);
                if ($res['data']['status'] == 0) {
                    $data['status'] = 1;
                    $data['validity_time'] = strtotime(date('Y-m-d', strtotime("+ {$res['data']['validity_time']}month")));
                    $data['remarks'] = "微信";
                    $comboAccess->do_update(['order_sn' => $result['out_trade_no']], $data);
                }
            } catch (Exception $e) {
                $rs = false;
            }
            if ($rs == true) {
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
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $config = json_decode(json_encode(yii::$app->params['wx_config']), false);
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
        $array = result(200, "请求成功", $arr);

        return $array;
    }

}
