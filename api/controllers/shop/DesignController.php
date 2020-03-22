<?php

namespace app\controllers\shop;
use yii\web\Controller;

class DesignController extends  Controller{
    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    public function actionEdit(){
        $request = request();
        $redis =  \Yii::$app->redis;
        $result = $redis->set('design',json_encode($request['params']));
        if($result){
            return result(200,'保存成功');
        }else{
            return result(-100,'保存失败');
        }
    }
    public function actionGet(){
        $request = request();
        $redis =  \Yii::$app->redis;
        $result = $redis->get('design');
        if($request){
            return result(200,'获取成功',json_decode($result,true));
        }else{
            return result(-100,'获取失败');
        }
    }
    public function actionTest(){
        $request = request();
        $url =   'http://test.lcsw.cn:8045/lcsw'.'/pay/100/minipay';
        $data = [
          'pay_ver'=>"100",
            'pay_type'=>"010",
            'service_id'=>"015",
            'merchant_no'=>"812405813000001",
            'terminal_id'=>"30056619",
            'terminal_trace'=>'O2019000001',
            'terminal_time'=>date("YmdHis"),
            'total_fee'=>"10",
        ];
        $sign_str = http_build_query($data) .'&access_token=a8d9a62802504d1399e90e7ded764ca1';
//        $data['sub_appid'] = 'wxe8bceb47d563824d';
//        $data['open_id'] = 'oQiQX0W1jfF6GdhDPdsEKYSVSAK0';//$request['open_id'];
//        $data['order_body']='测试订单';
//        $data['notify_url']="https://api2.juanpao.com/shop/design/go";
//        $data['attach']='1';
        $data['key_sign']= md5($sign_str);
//        $str  =curlPostJson($url,$data);
        var_dump($url);
        var_dump($data);
        return;
    }
}