<?php

/**
 * Created by 卷泡
 * author: JYS <272074691@qq.com>
 * Created DateTime: 2018/8/27 9:03
 */

namespace app\controllers\wechat\officialAccount;

use yii;
use yii\web\Controller;
use EasyWeChat\Factory;

class QrcodeController extends Controller
{
    public $enableCsrfValidation = false;//禁用CSRF令牌验证，可以在基类中设置
    public $wechat_type = 'oa';

    /**
     * 控制器默认临时二维码url
     */
    public function actionIndex()
    {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }
        $expireSeconds = 6 * 24 * 3600;
        $params = $request['params'];
        if (isset($params['expireSeconds'])) {
            $expireSeconds = $params['expireSeconds'];
        }

        //获取公众号实例 必须
        $app = $this->getApp($this->wechat_type);

        $res = $app->qrcode->temporary('temporary', $expireSeconds);
        $url = $app->qrcode->url($res['ticket']);
//        $content = file_get_contents($url); // 得到二进制图片内容 线上环境测试
        return result('200', '请求成功', $url);
    }

    /**
     * 获取公众号永久二维码url
     */
    public function actionForever()
    {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }

        //获取公众号实例 必须
        $app = $this->getApp($this->wechat_type);

        $res = $app->qrcode->forever('forever');
        $url = $app->qrcode->url($res['ticket']);
//        $content = file_get_contents($url); // 得到二进制图片内容 线上环境测试
        return result('200', '请求成功', $url);
    }
}
