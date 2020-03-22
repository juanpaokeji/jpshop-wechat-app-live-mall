<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/4/12 14:58
 */

namespace app\models\core;

use yii;
use EasyWeChat\Factory;

class EasyWechatModel {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    //第三方平台配置信息
    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA',
    ];

    function __construct($config, $array = array()) {
        if (isset($config['type'])) {
            if ($config['type'] == 1) {
                //手动配置信息实现公众号业务
                if ($count($array) != 0) {
                    $config['oauth'] = $array;
                }
                $app = Factory::openPlatform($config);
                return $app;
            } else if ($config['type'] == 2) {
                //带公众号实现业务
                $con = $this->config;
                if (count($array) != 0) {
                    $con['oauth'] = $array;
                }
                $openPlatform = Factory::openPlatform($con);
                $app = $openPlatform->officialAccount($config['app_id'], $config['refresh_token']);
                return $app;
            } else {
                return false;
            }
        } else {
            //带小程序实现业务
            $openPlatform = Factory::openPlatform($this->config);
            $app = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
            var_dump($config);
            die();
            return $app;
        }
    }



}
