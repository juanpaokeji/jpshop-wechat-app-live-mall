<?php

namespace app\controllers\admin\system;

use tools\pay\mini_pay\MiniPay;
use tools\pay\Payx;
use yii\web\CommonController;
use yii;
use yii\log\FileTarget;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');
include dirname(dirname(dirname(__DIR__))).'/extend/tools/pay/MiniPay/MiniPay.php';
include dirname(dirname(dirname(__DIR__))).'/extend/tools/pay/Pay.php';

class PayController extends CommonController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\TokenFilter', //调用过滤器
                'except' => ['pay','notify','openid'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 预支付订单创建
     * @return array|\tools\pay\mini_pay\MiniPayRe
     */
    public function actionPay()
    {
        if (yii::$app->request->isPost) {
            $request_body = file_get_contents('php://input');
            $data = json_decode($request_body, true);
            $mini_pay = new MiniPay();
            $mini_pay->setPay_ver(Payx::PAY_VER);
            $mini_pay->setPay_type("010");
            $mini_pay->setService_id(Payx::SERVICE_ID);
            $mini_pay->setMerchant_no(Payx::MERCHANT_NO);
            $mini_pay->setTerminal_id(Payx::TERMINAL_ID);
            $mini_pay->setTerminal_trace("000229");
            $mini_pay->setTerminal_time(date("YmdHis"));
            $mini_pay->setTotal_fee("1");
            $mini_pay->setOpen_id($data['openid']);
            $mini_pay->setNotify_url("http://api2.juanpao.com/admin/system/pay/notify");
            return result(200,Payx::miniPayRe($mini_pay));
        } else {
            return result(500, "请求方式错误");
        }
    }


    /**
     * 获取openid
     * @return array
     */
    public function actionOpenid()
    {
        if (yii::$app->request->isPost) {
            $appid = "wxb1d07a2d8ae4c0fb";
            $secret = "badd2c0ce20cc5ee4304d51fc3b39104";
            $request_body = file_get_contents('php://input');
            $data = json_decode($request_body, true);
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=".$appid."&secret=".$secret."&js_code=".$data['code']."&grant_type=authorization_code";
            return result(200,Payx::getOpenidFromMp($url));
        }else {
            return result(500, "请求方式错误");
        }
    }
    /**
     * 支付回调
     */
    public function actionNotify()
    {
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);
        file_put_contents(dirname(dirname(dirname(__DIR__))).'/web/pay1.text',$data["result_code"].PHP_EOL, FILE_APPEND);
        if(empty($data) || !is_array($data)){
            return result(200, ["return_code"=>"01","return_msg"=>"缺少数据"]); //
        }
        $data_json = \Yii::$app->redis->get($data['out_trade_no']);
        if($data_json){
            return result(200, ["return_code"=>"01","return_msg"=>"success"]); // 已存在 直接返回
        }else{
            // 处理业务
            if(isset($data["result_code"]) && $data["result_code"] == "01"){ //表示成功
                file_put_contents(dirname(dirname(dirname(__DIR__))).'/web/pay2.text',json_encode($data).PHP_EOL, FILE_APPEND);
                if(\Yii::$app->redis->set($data["out_trade_no"],"1")){
                    return result(200, ["return_code"=>"01","return_msg"=>"success"]);
                };
            }else{
                // 错误处理
            }
        }
    }
}
