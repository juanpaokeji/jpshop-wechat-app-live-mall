<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/18 9:03
 */

namespace app\controllers;

use yii;
use EasyWeChat\Factory;
use yii\web\MerchantController;

class WxController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置 必须设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                //    'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['index'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionIndex() {
        $signature = $_GET['signature'];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $config = $this->getSystemConfig($_GET['key'], "wechat");
        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        $tmpArr = array($config['token'], $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);  
        if ($tmpStr == $signature) {
            echo $_GET['echostr'];
        } else {
            return false;
        }

        //$this->broadCasting();
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

}
