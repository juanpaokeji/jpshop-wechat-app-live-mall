<?php

/**
 * Created by 卷泡
 * author: 杨靖 <272074691@qq.com>
 * Created DateTime: 2018/4/18 9:03
 */

namespace app\controllers\wechat\officialAccount;

use yii;
use EasyWeChat\Factory;
use yii\web\MerchantController;
use app\models\system\SystemAutoWordsModel;
use EasyWeChat\Kernel\Messages\Image;
use app\models\merchant\app\AppAccessModel;

define('TOKEN', 'juanPao');

class MessageController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                //    'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['index'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 控制器默认显示菜单列表 get 请求
     * @return array|void
     */
//    public function actionIndex() {
//        $key = $_GET['key'];
//       // $this->responseMsg($key);
//    }

    /**
     * 消息及事件入口文件
     * @return array|string|bool
     * @throws
     */
    public function actionIndex() {
        //获取微信配置信息
        $config = $this->getSystemConfig($params['key'], "wechat");
        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        //获取公众号实例 必须
        $app = $this->getApp($config);
        $app->server->push(function ($message) {
            $model = new AppAccessModel();
            $arr['`key`'] = $_GET['key'];
            $rs = $model->find($arr);

            if ($rs['status'] != 200) {
                return "未找到商户信息";
            }
            switch ($message['MsgType']) {
                case 'event':
                    //auto_type 类型 1=关注后回复 2=收到消息回复 3=关键词回复
                    //reply_type 回复类型 1=文字 2=图片 3=图文
                    if ($message['Event'] == "subscribe") {
                        $data['auto_type'] = 1;
                        $data['merchant_id'] = $rs['data']['merchant_id'];
                        $message = $this->getFind($data);
                        return $message;
                    }
                    if ($message['Event'] == "CLICK") {
                        $data['words'] = $message['EventKey'];
                        $data['auto_type'] = 3;
                        $data['merchant_id'] = $rs['data']['merchant_id'];
                        $message = $this->getFind($data);
                        return $message;
                    }
                    return '收到事件消息';
                    break;
                case 'text':
                    $data['words'] = $message['Content'];
                    $data['auto_type'] = 3;
                    $data['merchant_id'] = $rs['data']['merchant_id'];
                    $message = $this->getFind($data);
                    return $message;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                default:
                    return '收到其它消息';
                    break;
            }
        });

        $app->server->serve()->send();
    }

    public function getFind($data) {
//        //获取公众号实例 必须
//        $app = $this->getApp($this->wechat_type);
        $wordModel = new SystemAutoWordsModel();
        $rs = $wordModel->find($data);
        // $message = json_encode($rs, JSON_UNESCAPED_UNICODE);
        if ($rs['status'] == 200) {
            if ($rs['data']['reply_type'] == 1) {
                $message = $rs['data']['content'];
            } else if ($rs['data']['reply_type'] == 2) {
                $message = new Image($rs['data']['media_id']);
            } else {
                $message = "消息为空";
            }
        } else {
            $message = "3123123";
        }
        return $message;
    }

    public function getApp($config) {
        $app = Factory::officialAccount($config);
        return $app;
    }

}
