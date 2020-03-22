<?php

namespace app\controllers\common;

use yii;
use yii\web\Controller;

class CommonController extends Controller {

    function __construct() {
        
    }

    public function logistics() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $redis = getConfig($params['nu']);
            if (!$redis) {
                $nu = $params['nu'];
                $dateTime = gmdate("D, d M Y H:i:s T");
                $SecretId = 'AKID6p5FKDFI7gaFP9W1p85PUDYIBKT539eGC74q';
                $SecretKey = 'LzUey5Moflj039nMy0e6dicdp6cy6v972nf7y29e';
                $srcStr = "date: " . $dateTime . "\n" . "source: " . "source";
                $Authen = 'hmac id="' . $SecretId . '", algorithm="hmac-sha1", headers="date source", signature="';
                $signStr = base64_encode(hash_hmac('sha1', $srcStr, $SecretKey, true));
                $Authen = $Authen . $signStr . "\"";

                $url = "https://service-6t1c9ush-1255468759.ap-shanghai.apigateway.myqcloud.com/release/point-list?com=auto&nu={$nu}";
                $headers = array(
                    'Host:service-6t1c9ush-1255468759.ap-shanghai.apigateway.myqcloud.com',
                    'Accept: */*',
                    'Source: source',
                    'Date: ' . $dateTime,
                    'Authorization: ' . $Authen,
                    'X-Requested-With: XMLHttpRequest',
                    'Accept-Encoding: gzip, deflate, sdch'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
                $data = curl_exec($ch);

                if ($data['showapi_res_body']['status'] == 4) {
                    setConfig($params['nu'], $data);
                    yii::$app->redis->expire($params['nu'], 2592000);
                } else {
                    setConfig($params['nu'], $data);
                    yii::$app->redis->expire($params['nu'], 7200);
                }
                return result(200, "请求成功", $data);
            } else {
                return result(200, "请求成功", $redis);
            }
        }
    }

    public function generateCode($nums = 1, $exist_array = '', $code_length = 32, $prefix = '') {

        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";

        for ($j = 0; $j < $nums; $j++) {
            $code = '';
            for ($i = 0; $i < $code_length; $i++) {

                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
            $vou = new \app\models\shop\VoucherModel();
            $data = $vou->find(['cdkey' => $code]);
            if ($data['status'] == 200) {
                $promotion_codes[] = $data['data'];
            } else {
                $promotion_codes = array();
            }

            if (!in_array($code, $promotion_codes)) {
                if (is_array($exist_array)) {
                    if (!in_array($code, $exist_array)) {//排除已经使用的优惠码
                        $promotion_codes[$j] = $prefix . $code; //将生成的新优惠码赋值给promotion_codes数组
                    } else {
                        $j--;
                    }
                } else {
                    $promotion_codes[$j] = $prefix . $code; //将优惠码赋值给数组
                }
            } else {
                $j--;
            }
        }
        return $promotion_codes[0];
    }

    public function order_sn() {
        $order_date = date('Y-m-d');

        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN） $order_id_main = date('YmdHis') . rand(10000000,99999999);
        //订单号码主体长度 $order_id_len = strlen($order_id_main);

        $order_id_sum = 0;

        for ($i = 0; $i < $order_id_len; $i++) {

            $order_id_sum += (int) (substr($order_id_main, $i, 1));
        }

        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
        return $order_id;
    }

}
