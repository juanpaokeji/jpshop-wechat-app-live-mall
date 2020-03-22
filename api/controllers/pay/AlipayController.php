<?php

namespace app\controllers\pay;

use yii;
use yii\web\Controller;
use yii\db\Exception;
use alipay\Alipay;
use alipay\AlipayTradeService;
use app\models\merchant\app\AppAccessModel;
use app\models\merchant\pay\PayModel;
use app\models\merchant\app\ComboModel;
use app\models\merchant\forum\ForumModel;
use app\models\pay\AlipayModel;
use app\models\forum\UserModel;
use app\models\system\SystemWxConfigModel;
use app\models\merchant\app\AppModel;
use app\models\merchant\system\GroupModel;

require_once Yii::getAlias('@vendor/alipay/pagepay/Alipay.php');
require_once Yii::getAlias('@vendor/alipay/pagepay/service/AlipayTradeService.php');

/**
 * 阿里支付控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class AlipayController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/阿里支付/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionIndex($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(500, "缺少请求参数 订单号");
            }
            $alipay = new Alipay();
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
            $appModel = new AppModel();
            $app = $appModel->find(['id' => $appAccess['data']['app_id']]);
            if ($app['status'] == 200) {
                if ($app['data']['category_id'] == 1) {
                    $data['WIDout_trade_no'] = "forum_" . $params['id'];
                } else if ($app['data']['category_id'] == 2) {
                    $data['WIDout_trade_no'] = "shop_" . $params['id'];
                }
            } else {
                return result(500, "找不到APP信息");
            }

            $data['WIDsubject'] = $combo['data']['name'];

            //付款金额，必填
            // $data['WIDtotal_amount'] = $payinfo['data']['remain_price'];
            $data['WIDtotal_amount'] = 0.01;
            //商品描述，可空
            $data['WIDbody'] = $combo['data']['name'];
            $alipay->pagepay($data, yii::$app->params['ali_config']);
            die();
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionQuery() {
        $alipay = new Alipay();
        //商户订单编号
        $data['WIDTQout_trade_no'] = "201804181524123774";
        //支付宝订单编号
        $data['WIDTQtrade_no'] = "";
        $rs = $alipay->query($data, yii::$app->params['ali_config']);
        print_r(json_decode(json_encode($rs), true));
    }

    public function actionRefund() {
        $alipay = new Alipay();

        $data['WIDTRtrade_no'] = '2018041921001004650283400125';
        $data['WIDTRout_trade_no'] = '201804181524123774';
        //需要退款的金额，该金额不能大于订单金额，必填
        $data['WIDTRrefund_amount'] = 0.01;

        //退款的原因说明
        $data['WIDTRrefund_reason'] = '退款测试';

        //标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
        $data['WIDTRout_request_no'] = "20180420" . time();

        $rs = $alipay->refund($data, yii::$app->params['ali_config']);
        print_r(json_decode(json_encode($rs), true));
    }

    public function actionRefundquery() {
        $alipay = new Alipay();

        $data['WIDTRtrade_no'] = '2018041921001004650283400125'; //商户订单号
        $data['WIDTRout_trade_no'] = '201804181524123774'; //支付包订单号
        $data['WIDRQout_request_no'] = ''; //退款标识
        $rs = $alipay->refundquery($data, yii::$app->params['ali_config']);
        print_r(json_decode(json_encode($rs), true));
    }

    public function actionClose() {
        $alipay = new Alipay();

        $data['WIDTRtrade_no'] = '2018041921001004650283400125'; //商户订单号
        $data['WIDTRout_trade_no'] = '201804181524123774'; //支付包订单号
        $rs = $alipay->close($data, yii::$app->params['ali_config']);
        print_r(json_decode(json_encode($rs), true));
    }

    public function actionReturn_url() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->post(); //获取地址栏参数
            $alipaySevice = new AlipayTradeService(yii::$app->params['ali_config']);

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
                $out_trade_no = $params['out_trade_no'];
                //支付宝交易号
                $trade_no = $params['trade_no'];

                $payModel = new PayModel();
                $out_trade_no = explode("_", $out_trade_no);
                $payData['id'] = $out_trade_no[1];
                $payData['type'] = 2;
                $payData['status'] = 1;
                $payData['pay_time'] = time();
                $payData['transaction_id'] = $trade_no;
                $payModel->update($payData);
                $payinfo = $payModel->find(['id' => $out_trade_no[1]]);
                if ($out_trade_no[0] == "forum") {
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
                    $systemConfigdata['`key`'] = $apppAccess['data']['key'];
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
                    $systemConfigdata['wechat_info'] = '{"name":"","app_id":"",app_secret:0,"account":"","type":"","describe":"","wechat_id":"","head_img":"","qrcode_url":""}';
                    $systemConfigModel->add($systemConfigdata);
                } else if ($out_trade_no[0] == "shop") {
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
                        "secret" => "",
                        "url" => "https://api.juanpao.com/wx?key={$appAccess['data']['key']}",
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
                }


                $aliModel = new AlipayModel();
                unset($params['charset']);
                unset($params['method']);
                unset($params['version']);
                $rs = $aliModel->add($params);
                return $rs;

                //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            } else {
                return result(500, "支付失败");
            }
        }
    }

    public function notify_url() {
        $arr = $_POST;
        $alipaySevice = new AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($_POST, true));
        $result = $alipaySevice->check($arr);

        /* 实际验证过程建议商户添加以下校验。
          1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号，
          2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额），
          3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）
          4、验证app_id是否为该商户本身。
         */
        if ($result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代
            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号

            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];


            if ($_POST['trade_status'] == 'TRADE_FINISHED') {

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序
                //注意：
                //退款日期超过可退款期限后（如三个月可退款），支付宝系统发送该交易状态通知
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //请务必判断请求时的total_amount与通知时获取的total_fee为一致的
                //如果有做过处理，不执行商户的业务程序			
                //注意：
                //付款完成后，支付宝系统发送该交易状态通知
            }
            $params = $_POST;
            $out_trade_no = $params['out_trade_no'];
            //支付宝交易号
            $trade_no = $params['trade_no'];

            $payModel = new PayModel();
            $out_trade_no = explode("_", $out_trade_no);
            $payData['id'] = $out_trade_no[1];
            $payData['type'] = 2;
            $payData['status'] = 1;
            $payData['pay_time'] = time();
            $payData['transaction_id'] = $trade_no;
            $payModel->update($payData);
            $payinfo = $payModel->find(['id' => $out_trade_no[1]]);
            if ($out_trade_no[0] == "forum") {
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
                    'illegally' => 0,
                );
                $array = array(
                    '`key`' => $apppAccess['data']['key'],
                    'name' => $apppAccess['data']['name'],
                    'merchant_id' => $apppAccess['data']['merchant_id'],
                    'pic_url' => $apppAccess['data']['pic_url'],
                    'detail_info' => $apppAccess['data']['detail_info'],
                    'config' => json_encode($array),
                    'status' => 1,
                );
                $forumModel->add($data);
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
                $systemConfigdata['key'] = $apppAccess['data']['key'];
                $systemConfigdata['merchant_id']['wechat']['url'] = "https://api.juanpao.com/wx?key={$appAccess['data']['key']}";
                $systemConfigdata['wehcat'] = json_encode(array(
                    "type" => 0,
                    "app_id" => "",
                    "secret" => "",
                    "token" => generateCode(32),
                    "aes_key" => generateCode(43),
                    'scopes' => ['snsapi_userinfo'],
                    'callback' => "/shop/users/callback?key={$appAccess['data']['key']}",
                ));
                $systemConfigdata['miniprogram'] = "";
                $systemConfigModel->add($systemConfigdata);
            } else if ($out_trade_no[0] == "shop") {
                $appAccessModel = new AppAccessModel();
                $appAccess = $appAccessModel->find(['id' => $payinfo['data']['app_access_id']]);
                $comboModel = new ComboModel();
                $comboinfo = $comboModel->find(['id' => $appAccess['data']['combo_id']]);
                $data['expire_time'] = strtotime(date('Y-m-d', strtotime("+{$comboinfo['data']['expired_days']}day")));
                $data['id'] = $payinfo['data']['app_access_id'];
                $data['status'] = 1;
                $appAccessModel = new AppAccessModel();
                $rs = $appAccessModel->update($data);

                $systemConfigModel = new SystemWxConfigModel();
                $systemConfigdata['merchant_id'] = $appAccess['data']['merchant_id'];
                $systemConfigdata['key'] = $apppAccess['data']['key'];
                $systemConfigdata['merchant_id']['wechat']['url'] = "https://api.juanpao.com/wx?key={$appAccess['data']['key']}";
                $systemConfigdata['wehcat'] = json_encode(array(
                    "type" => 0,
                    "app_id" => "",
                    "secret" => "",
                    "token" => generateCode(32),
                    "aes_key" => generateCode(43),
                    'scopes' => ['snsapi_userinfo'],
                    'callback' => "/shop/users/callback?key={$appAccess['data']['key']}",
                ));
                $systemConfigdata['miniprogram'] = "";
                $systemConfigModel->add($systemConfigdata);
            }
            $aliModel = new AlipayModel();
            unset($params['charset']);
            unset($params['method']);
            unset($params['version']);
            $aliModel->add($params);
            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
            echo "success"; //请不要修改或删除
            die();
        } else {
            //验证失败
//            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);
//            //支付宝交易号
//            $trade_no = htmlspecialchars($_GET['trade_no']);
//            $payModel = new PayModel();
//            $params['id'] = $out_trade_no;
//            $params['type'] = 2;
//            $params['status'] = 0;
//            $params['pay_time'] = time();
//            $params['transaction_id'] = $trade_no;
//            $payModel->update($params);
//
//
//            $payinfo = $payModel->find(['id' => $out_trade_no]);
//            $data['expire_time'] = time();
//            $data['id'] = $payinfo['data']['app_access_id'];
//
//            $appAccessModel = new AppAccessModel();
//            $rs = $appAccessModel->update($data);
            echo "fail";
            die();
        }
    }

}
