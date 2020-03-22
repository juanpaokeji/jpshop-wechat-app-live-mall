<?php

/**
 * Created by 卷泡
 * author: 杨靖 <272074691@qq.com>
 * Created DateTime: 2018/4/18 9:03
 */

namespace app\controllers\wechat\officialAccount;

use app\models\merchant\system\OperationRecordModel;
use yii;
use EasyWeChat\Factory;
use yii\web\MerchantController;
use app\models\system\SystemWxConfigModel;
use app\models\system\SystemAutoWordsModel;
use app\models\system\SystemAppAccessVersionModel;
use app\models\system\SystemAppVersionModel;
use app\models\merchant\app\AppModel;
use app\models\merchant\app\AppAccessModel;

class OpenplatController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
                //    'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['message', 'mini', 'notice', 'test'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA'
    ];

    public function actionIndex() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }

        $params = $request['params'];
        if (!isset($params['type'])) {
            return result(500, "缺少参数 type");
        }
        $openPlatform = Factory::openPlatform($this->config);
        $openPlatform->server->serve();
        $token = yii::$app->request->getHeaders()->get('Access-Token'); //获取头部 token
        $url = $openPlatform->getPreAuthorizationUrl("https://web.juanpao.com/{$params['type']}/codeReturn.html"); // 传入回调URI即可
        //$this->redirect($url);

        return result(200, "成功", $url);
    }

    public function actionNotice() {
        $openPlatform = Factory::openPlatform($this->config);
        $server = $openPlatform->server;
        $messgae = $server->getMessage();
        //   setConfig("component_verify_ticket", $messgae['ComponentVerifyTicket']);
//        $server->push(function ($message) {
//            $systemWxConfigModel = new SystemWxConfigModel();
//            $data['app_id'] = $message['AuthorizerAppid'];
//            $rs = $systemWxConfigModel->find($data);
////            if ($rs['status'] == 200) {
////                $params['id'] = $rs['data']['id'];
////                $systemWxConfigModel->delete($params);
////            }
//        }, Guard::EVENT_UNAUTHORIZED);
//        $this->logger(json_encode($messgae));
//        $this->logger($server->serve());
        return $server->serve();
    }

    public function actionCallback() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }
        $params = $request['params'];
        $openPlatform = Factory::openPlatform($this->config);
        $app = $openPlatform->handleAuthorize($params['auth_code']);
        if (!isset($app['authorization_info'])) {
            return result('500', '授权失败', $app);
        }
        $authorizer = $openPlatform->getAuthorizer($app['authorization_info']['authorizer_appid']);
        if (!isset($authorizer['authorizer_info'])) {
            return result('500', '授权失败', $app);
        }
        if (isset($authorizer['authorizer_info']['MiniProgramInfo'])) {
            $officialAccount = $openPlatform->miniProgram($authorizer['authorization_info']['authorizer_appid'], $authorizer['authorization_info']['authorizer_refresh_token']);
        } else {
            $officialAccount = $openPlatform->officialAccount($authorizer['authorization_info']['authorizer_appid'], $authorizer['authorization_info']['authorizer_refresh_token']);
        }

        $account = $officialAccount->account;
        // $result = $account->getBinding();
        //查询配置信息
        $model = new SystemWxConfigModel();
        $data['key'] = $params['key'];
        $data['merchant_id'] = yii::$app->session['uid'];
        $systemWxConfigModel = $model->find($data);
        if ($systemWxConfigModel['status'] != 200) {
            return result('500', '授权失败,未找到商户信息');
        }

        if ($systemWxConfigModel['data']['open_app_id'] == "") {
            $result = $account->create();
            if ($result['errcode'] != 0) {
                $result = $account->getBinding();
                if ($result['errcode'] != 0) {
                    return result('500', '获取开放平台id失败');
                }
            }
        } else {
            $result['open_appid'] = $systemWxConfigModel['data']['open_app_id'];
            $account->bindTo($systemWxConfigModel['data']['open_app_id']);
        }

        //判断是否是小程序
        if (isset($authorizer['authorizer_info']['MiniProgramInfo'])) {
            //判断是否绑定 
            $systemWxConfig = json_decode($systemWxConfigModel['data']['miniprogram'], true);

//            if ($systemWxConfigModel['data']['miniprogram'] == "") {
//                $result = $account->create();
//            }
            $data['miniprogram'] = array(
                "app_id" => $authorizer['authorization_info']['authorizer_appid'],
                "nick_name" => $authorizer['authorizer_info']['nick_name'],
                "head_img" => isset($authorizer['authorizer_info']['head_img']) ? $authorizer['authorizer_info']['head_img'] : "",
                "func_info" => $authorizer['authorization_info']['func_info'],
                "service_type" => $authorizer['authorizer_info']['service_type_info']['id'],
                "verify_type" => $authorizer['authorizer_info']['verify_type_info']['id'],
                "wechat_id" => $authorizer['authorizer_info']['user_name'],
                "principal_name" => $authorizer['authorizer_info']['principal_name'],
                "qrcode_url" => $authorizer['authorizer_info']['qrcode_url'],
                "type" => isset($authorizer['authorizer_info']['MiniProgramInfo']) ? 1 : 2,
                'access_token' => $app['authorization_info']['authorizer_access_token'],
                "refresh_token" => $authorizer['authorization_info']['authorizer_refresh_token'],
                "status" => 1,
                "remark" => "",
            );

            $data['miniprogram']['open_appid'] = $result['open_appid'];
            $data['miniprogram_id'] = $authorizer['authorization_info']['authorizer_appid'];
            $data['miniprogram'] = json_encode($data['miniprogram'], JSON_UNESCAPED_UNICODE);
        } else {
            $systemWxConfig = json_decode($systemWxConfigModel['data']['wechat'], true);
            $data['wechat'] = array(
                "app_id" => $authorizer['authorization_info']['authorizer_appid'], //授权的appid
                "nick_name" => $authorizer['authorizer_info']['nick_name'], //公众号名称
                "head_img" => isset($authorizer['authorizer_info']['head_img']) ? $authorizer['authorizer_info']['head_img'] : "", //公众号头像
                "func_info" => $authorizer['authorization_info']['func_info'], //权限 json格式
                "service_type" => $authorizer['authorizer_info']['service_type_info']['id'], //授权方公众号类型，0=订阅号 1=由历史老帐号升级后的订阅号 2=服务号
                "verify_type" => $authorizer['authorizer_info']['verify_type_info']['id'], //授权方认证类型，-1代表未认证，0代表微信认证，1代表新浪微博认证，2代表腾讯微博认证，3代表已资质认证通过但还未通过名称认证，4代表已资质认证通过、还未通过名称认证，但通过了新浪微博认证，5代表已资质认证通过、还未通过名称认证，但通过了腾讯微博认证
                "wechat_id" => $authorizer['authorizer_info']['user_name'], //授权方公众号的原始ID
                "principal_name" => $authorizer['authorizer_info']['principal_name'], //公众号的主体名称
                "qrcode_url" => $authorizer['authorizer_info']['qrcode_url'], //二维码图片的URL
                "type" => isset($authorizer['authorizer_info']['MiniProgramInfo']) ? 1 : 2, //授权类型 2=微信 1=小程序
                'access_token' => $app['authorization_info']['authorizer_access_token'], //暂无使用，easyWechat 放在缓存
                "refresh_token" => $authorizer['authorization_info']['authorizer_refresh_token'], //刷新令牌
                "status" => 1,
                "remark" => "",
            );


            $data['wechat'] = json_encode($data['wechat'], JSON_UNESCAPED_UNICODE);
            $type = 0;
            if ($authorizer['authorizer_info']['verify_type_info']['id'] == -1) {
                if ($authorizer['authorizer_info']['service_type_info']['id'] == 0 || $authorizer['authorizer_info']['service_type_info']['id'] == 1) {
                    $type = 1;
                }
                if ($authorizer['authorizer_info']['service_type_info']['id'] == 2) {
                    $type = 2;
                }
            } else {
                if ($authorizer['authorizer_info']['service_type_info']['id'] == 0 || $authorizer['authorizer_info']['service_type_info']['id'] == 1) {
                    $type = 3;
                }
                if ($authorizer['authorizer_info']['service_type_info']['id'] == 2) {
                    $type = 4;
                }
            }
            $data['app_id'] = $authorizer['authorization_info']['authorizer_appid'];
            $data['wechat_info'] = json_encode(array(
                "account" => $authorizer['authorizer_info']['alias'],
                "app_id" => $authorizer['authorization_info']['authorizer_appid'],
                "name" => $authorizer['authorizer_info']['nick_name'],
                "head_img" => isset($authorizer['authorizer_info']['head_img']) ? $authorizer['authorizer_info']['head_img'] : "",
                "wechat_id" => $authorizer['authorizer_info']['user_name'],
                "describe" => $authorizer['authorizer_info']['signature'],
                "describe" => $authorizer['authorizer_info']['signature'],
                "type" => $type,
                "docking_type" => "2",
                "head_img" => isset($authorizer['authorizer_info']['head_img']) ? $authorizer['authorizer_info']['head_img'] : "",
                "qrcode_url" => $authorizer['authorizer_info']['qrcode_url'],
                    ), JSON_UNESCAPED_UNICODE);
        }
        $data['open_app_id'] = $result['open_appid']; //公众平台绑定id
        $data['id'] = $systemWxConfigModel['data']['id'];

        $rs = $model->update($data);

        $model = new SystemWxConfigModel();
        $system['key'] = $data['key'];
        $system['merchant_id'] = yii::$app->session['uid'];
        $systemConfig = $model->find($system);
        setConfig($data['key']);
        if ($systemConfig['status'] == 200) {
            $array['wechat'] = $systemConfig['data']['wechat'];
            $array['miniprogram'] = $systemConfig['data']['miniprogram'];
            $array['wxpay'] = $systemConfig['data']['wechat_pay'];
            setConfig($data['key'], $array);
        } else {
            return result('500', '授权失败,请重新授权');
        }
        if ($rs['status'] == 200) {
            return result('200', '授权成功', isset($authorizer['authorizer_info']['MiniProgramInfo']) ? 1 : 2);
        } else {
            return result('500', '授权失败');
        }
    }

    public function actionMessage() {

        $appId = $_GET['appid'];
        //  $this->logger(json_encode($_GET));
        $appId = substr($appId, 1);
        $openPlatform = Factory::openPlatform($this->config);
        $officialAccount = $openPlatform->officialAccount($appId);
        $server = $officialAccount->server; // ❗️❗️  这里的 server 为授权方的 server，而不是开放平台的 server，请注意！！！

        $server->push(function ($message) {
            //   $this->logger(json_encode($message));
//        $_GET = json_decode('{"appid":"\/wxad97302859af8578","signature":"ce8a194f929c3dc4741c20fbb273c396bac31014","timestamp":"1563507355","nonce":"1670683142","openid":"o9jMp45uhE4RGvTA_lePf32IwdW0","encrypt_type":"aes","msg_signature":"6a7d2d0f8d659c1b8f8025bd7e2bd7c23e087686"}', true);
//        $message = json_decode('{"ToUserName":"gh_340b227f1688","FromUserName":"o9jMp45uhE4RGvTA_lePf32IwdW0","CreateTime":"1563507354","MsgType":"event","Event":"weapp_audit_success","SuccTime":"1563507354"}', true);
//
//        $appId = $_GET['appid'];
//        $appId = substr($appId, 1);

            switch ($message['MsgType']) {
                case 'event':
                    //auto_type 类型 1=关注后回复 2=收到消息回复 3=关键词回复
                    //reply_type 回复类型 1=文字 2=图片 3=图文
                    if ($message['Event'] == "subscribe") {
                        $rs = $this->getConfig("wechat", $_GET);
                        $data['auto_type'] = 1;
                        $data['merchant_id'] = $rs['data']['merchant_id'];
                        $message = $this->getFind($data);
                        return $message;
                        break;
                    }
                    if ($message['Event'] == "CLICK") {
                        $rs = $this->getConfig("wechat", $_GET);
                        $data['words'] = $message['EventKey'];
                        $data['auto_type'] = 3;
                        $data['merchant_id'] = $rs['data']['merchant_id'];
                        $message = $this->getFind($data);
                        return $message;
                        break;
                    }
                    if ($message['Event'] == "weapp_audit_success") {
                        $rs = $this->getConfig("miniprogram", $_GET);
                        $this->logger(json_encode($rs));
                        $data['merchant_id'] = $rs['data']['merchant_id'];
                        $data['`key`'] = $rs['data']['key'];
                        $saavModel = new SystemAppAccessVersionModel();
                        $arr = $saavModel->LastFind($data);
                        $this->logger(json_encode($data));
                        $data['id'] = $arr['data']['id'];
                        $data['status'] = 5;
                        $kkk = $saavModel->update($data);
                        $this->logger(json_encode($data));
                        return true;
                        break;
                    }
                    if ($message['Event'] == "weapp_audit_fail") {
                        $rs = $this->getConfig("miniprogram", $_GET);
                        $this->logger(json_encode($rs));
                        $data['merchant_id'] = $rs['data']['merchant_id'];
                        $data['`key`'] = $rs['data']['key'];
                        $saavModel = new SystemAppAccessVersionModel();
                        $arr = $saavModel->LastFind($data);
                        $this->logger(json_encode($data));
                        $data['id'] = $arr['data']['id'];
                        $data['status'] = 4;
                        $data['remarks'] = $message['Reason'];
                        $kkk = $saavModel->update($data);
                        $this->logger(json_encode($data));
                        return true;
                        break;
                    }
                case 'text':
                    $rs = $this->getConfig("wechat", $_GET);
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
                case 'file':
                    return '收到文件消息';
                default:
                    return '收到其它消息';
                    break;
            }
        });
        $server->serve()->send();
    }

    /**
     * 解除授权
     */
    public function actionRemove() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'DELETE') {
            return result('500', '请求失败');
        }
        $params = $request['params'];
        $must = ['key'];
        $rs = $this->checkInput($must, $params);
        if ($rs != false) {
            return json_encode($rs, JSON_UNESCAPED_UNICODE);
        }

        $systemWxConfigModel = new SystemWxConfigModel();
        $params['merchant_id'] = yii::$app->session['uid'];
        $wechat = $systemWxConfigModel->find($params);
        if ($wechat['status'] != 200) {
            return result(500, '未查询到授权信息');
        }

        $openPlatform = Factory::openPlatform($this->config);
        if ($params['type'] == "miniprogram") {
            // 代小程序实现业务
            $config = json_decode($wechat['data']['miniprogram'], true);
            $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
            $account = $miniProgram->account;
            $result = $account->unbindFrom($config['open_appid']);
            if ($result['errcode'] == 0) {
                $data['id'] = $wechat['data']['id'];
                $data['miniprogram'] = "";
                $data['key'] = $params['key'];
                $data['merchant_id'] = yii::$app->session['uid'];
                unset($params['merchant_id']);
                $rs = $systemWxConfigModel->update($data);
                $array['wechat'] = $wechat['data']['wechat'];
                $array['miniprogram'] = "";
                $array['wxpay'] = $wechat['data']['wechat_pay'];
                setConfig($data['key'], $array);
                return result('200', '解除授权成功');
            } else {
                return result('500', '解除授权失败');
            }
        } else if ($params['type'] == "wechat") {
            // 代公众号实现业务
            $config = json_decode($wechat['data']['wechat'], true);
            $officialAccount = $openPlatform->officialAccount($config['app_id'], $config['refresh_token']);
            $account = $officialAccount->account;
            $result = $account->unbindFrom($config['open_appid']);
            if ($result['errcode'] == 0) {
                $data['id'] = $wechat['data']['id'];
                $data['wechat'] = '{"type":0,"wechat_id":0,"app_id":"","url":"https://api.juanpao.com/wx?key=' . $params["key"] . '","secret":"","token":"","aes_key":""}';
                $data['wechat_info'] = '{"name":"","app_id":"","app_secret":0,"account":"","type":"","describe":"","wechat_id":"","head_img":"","qrcode_url":""}';
                $data['key'] = $params['key'];
                $data['merchant_id'] = yii::$app->session['uid'];
                unset($params['merchant_id']);
                $rs = $systemWxConfigModel->update($data);
                $array['wechat'] = $data['wechat'];
                $array['miniprogram'] = $wechat['data']['miniprogram'];
                $array['wxpay'] = $wechat['data']['wechat_pay'];
                setConfig($data['key'], $array);
                return result('200', '解除授权成功');
            } else {
                return result('500', '解除授权失败');
            }
        } else {
            return result('500', '解除授权失败');
        }
    }

    public function getFind($data) {
        $wordModel = new SystemAutoWordsModel();
        $rs = $wordModel->find($data);
        if ($rs['status'] == 200) {
            if ($rs['data']['reply_type'] == 1) {
                $message = $rs['data']['content'];
            } else if ($rs['data']['reply_type'] == 2) {
                $message = new Image($rs['data']['media_id']);
            } else {
                $message = "消息为空";
            }
        } else {
            $message = "消息为空";
        }
        return $message;
    }

    /**
     * 小程序上传
     */
    public function actionCommit() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'POST') {
            return result('500', '请求失败');
        }
        //获取微信配置信息
        $params = $request['params'];
        //第三方授权
        //小程序上传信息配置
        $config = $this->getSystemConfig($params['key'], "miniprogram");
        if ($config == false) {
            return result(500, "未配置小程序信息");
        }
        $AuthorizerToken = $this->getAuthorizerToken($config['app_id'], $config['refresh_token']);
        //查询最新的小程序版本                                                 
        //查询应用
        $appAccessModel = new AppAccessModel();
        $appAccessParams['`key`'] = $params['key'];
        $appAccessData = $appAccessModel->find($appAccessParams);
        if ($appAccessData['status'] != 200) {
            return result(200, "上传失败，应用信息不对应");
        }
        //商户小程序  获取最后一个上传审核的版本信息概况
        //获取第三方平台上传的小程序上传版本信息概况
        $systemAppVersionModel = new SystemAppVersionModel();
        $systemAppVersionParams['app_id'] = $appAccessData['data']['app_id'];
        $systemAppVersionParams['type'] = 2;
        $systemAppVersionData = $systemAppVersionModel->LastFind($systemAppVersionParams);
        if ($systemAppVersionData['status'] != 200) {
            return result(200, "上传失败，找不到小程序版本信息");
        }
        //带授权实现业务
        $access_token = $AuthorizerToken['authorizer_access_token'];
        $url = "https://api.weixin.qq.com/wxa/commit?access_token={$access_token}";
        $str = '{"extEnable":true,"extAppid":"' . $config['app_id'] . '","ext":{"key":"' . $params['key'] . '"},' . $systemAppVersionData['data']['ext_json'];
        $array = json_encode(array(
            "template_id" => $systemAppVersionData['data']['template_id'],
            "ext_json" => $str,
            "user_version" => $systemAppVersionData['data']['number'],
            "user_desc" => $params['describe']
                ), JSON_UNESCAPED_UNICODE);

        $rs = json_decode(curlPost($url, $array), true);
        if ($rs['errcode'] == 0) {
            $data = array(
                "`key`" => $params['key'],
                "app_id" => $appAccessData['data']['app_id'],
                "app_access_id" => $appAccessData['data']['id'],
                "merchant_id" => yii::$app->session['uid'],
                "combo_id" => $appAccessData['data']['combo_id'],
                "number" => $systemAppVersionData['data']['number'],
                "template_id" => $systemAppVersionData['data']['template_id'],
                "type" => 2,
                "return_id" => "",
                "status" => "1",
                "remarks" => ""
            );
            $systemAppAccessVersion = new SystemAppAccessVersionModel();
            $systemAppAccessVersion->add($data);
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['key'];
            $operationRecordData['merchant_id'] = yii::$app->session['uid'];
            $operationRecordData['operation_type'] = '更新';
            $operationRecordData['operation_id'] = $params['key'];
            $operationRecordData['module_name'] = '上传发布';
            $operationRecordModel->do_add($operationRecordData);
            return result(200, "上传成功", 1);
        } else {
            if ($rs['errcode'] == 42001) {
                $openPlatform = Factory::openPlatform($this->config);
                // 代小程序实现业务
                $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
                $token = $miniProgram->access_token->getToken(true); // 强制重新从微信服务器获取 token
                $miniProgram['access_token']->setToken($token['authorizer_access_token'], 3600);
            }
            return result(500, $rs, 2);
        }
    }

    /**
     * 获取体验小程序的体验二维码
     */
    public function actionQrcode() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }
        //获取微信配置信息
        $params = $request['params'];
        $config = $this->getSystemConfig($params['key'], "miniprogram");
        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        $AuthorizerToken = $this->getAuthorizerToken($config['app_id'], $config['refresh_token']);
        $access_token = $AuthorizerToken['authorizer_access_token'];
        $url = "https://api.weixin.qq.com/wxa/get_qrcode?access_token={$access_token}";
        $qrcode = $this->downloadImg($url, $config['app_id'], $config['refresh_token']);
        if ($qrcode == false) {
            return result(500, "请求失败,请稍后重新获取");
        }
        $qrcode = "https://api.juanpao.com/" . $qrcode;
        return result(200, "请求成功", $qrcode);
    }

    /**
     * 获取授权小程序帐号已设置的类目
     */
    public function getCategory($access_token) {

        $url = "https://api.weixin.qq.com/wxa/get_category?access_token={$access_token}";
        $category = json_decode(curlGet($url), true);
        return $category;
    }

    /**
     * 获取小程序的第三方提交代码的页面配置（仅供第三方开发者代小程序调用）
     */
    public function getPage($access_token) {

        $url = "https://api.weixin.qq.com/wxa/get_page?access_token={$access_token}";
        $page = json_decode(curlGet($url), true);
        return $page;
    }

    /**
     * 将第三方提交的代码包提交审核（仅供第三方开发者代小程序调用）
     */
    public function actionAudit() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'POST') {
            return result('500', '请求失败');
        }
        //获取微信配置信息
        $params = $request['params'];

        //查询最新的小程序版本                                                 
        //查询应用
        $appAccessModel = new AppAccessModel();
        $appAccessParams['`key`'] = $params['key'];
        $appAccessData = $appAccessModel->find($appAccessParams);
        if ($appAccessData['status'] != 200) {
            return result(200, "审核提交失败，应用信息不对应");
        }
        //商户小程序  获取最后一个上传审核的版本信息概况
        //获取第三方平台上传的小程序上传版本信息概况
        $systemAppAccessVersionModel = new SystemAppAccessVersionModel();
        $systemAppAccessVersionParams['app_id'] = $appAccessData['data']['app_id'];
        $systemAppAccessVersionParams['app_access_id'] = $appAccessData['data']['id'];
        $systemAppAccessVersionParams['merchant_id'] = yii::$app->session['uid'];
        $systemAppAccessVersionParams['type'] = 2;
        $systemAppAccessVersionParams['`key`'] = $params['key'];
        $systemAppAccessData = $systemAppAccessVersionModel->LastFind($systemAppAccessVersionParams);
        if ($systemAppAccessData['status'] != 200) {
            return result(200, "审核提交失败，找不到小程序版本信息");
        }

        //获取授权信息
        $config = $this->getSystemConfig($params['key'], "miniprogram");
        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        $AuthorizerToken = $this->getAuthorizerToken($config['app_id'], $config['refresh_token']);
        //带小程序实现审核
        $access_token = $AuthorizerToken['authorizer_access_token'];
        $url = "https://api.weixin.qq.com/wxa/submit_audit?access_token={$access_token}";
        $category = $this->getCategory($access_token);
        if ($category['errcode'] != 0) {
            return result(500, "审核失败，未获取到您的小程序分类信息", $category);
        }
        if(count($category['category_list'])==0){
            return result(500, "审核失败，未获取到您的小程序分类信息", $category);
        }
        $page = $this->getPage($access_token);
        if ($category['errcode'] != 0) {
            return result(500, "审核失败，未获取到您的小程序页面信息", $page);
        }

        $data['address'] = $page['page_list'][1];
        $data['tag'] = "小程序";
        $data['first_class'] = $category['category_list'][0]['first_class'];
        $data['second_class'] = $category['category_list'][0]['second_class'];
        $data['first_id'] = $category['category_list'][0]['first_id'];
        $data['second_id'] = $category['category_list'][0]['second_id'];
        $data['title'] = "小程序";
        $arr['item_list'][] = $data;
        $rs = curlPost($url, json_encode($arr, JSON_UNESCAPED_UNICODE));
        $rs = json_decode($rs, true);
        if ($rs['errcode'] == 0) {
            $saa['id'] = $systemAppAccessData['data']['id'];
            $saa['status'] = 3;
            $saa['return_id'] = $rs['auditid'];
            $systemAppAccessVersionModel->update($saa);
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['key'];
            $operationRecordData['merchant_id'] = yii::$app->session['uid'];
            $operationRecordData['operation_type'] = '更新';
            $operationRecordData['operation_id'] = $params['key'];
            $operationRecordData['module_name'] = '上传发布';
            $operationRecordModel->do_add($operationRecordData);
            return result(200, "审核提交成功", 3);
        } else {
            if ($rs['errcode'] == 42001) {
                $openPlatform = Factory::openPlatform($this->config);
                // 代小程序实现业务
                $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
                $token = $miniProgram->access_token->getToken(true); // 强制重新从微信服务器获取 token
                $miniProgram['access_token']->setToken($token['authorizer_access_token'], 3600);
            }
            return result(500, $rs, 4);
        }
    }

    /**
     * 发布已通过审核的小程序（仅供第三方代小程序调用）
     */
    public function actionRelease() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'POST') {
            return result('500', '请求失败');
        }
        //获取微信配置信息
        $params = $request['params'];

        //查询最新的小程序版本                                                 
        //查询应用
        $appAccessModel = new AppAccessModel();
        $appAccessParams['`key`'] = $params['key'];
        $appAccessData = $appAccessModel->find($appAccessParams);
        if ($appAccessData['status'] != 200) {
            return result(200, "审核提交失败，应用信息不对应");
        }
        //商户小程序  获取最后一个上传审核的版本信息概况
        //获取第三方平台上传的小程序上传版本信息概况
        $systemAppAccessVersionModel = new SystemAppAccessVersionModel();
        $systemAppAccessVersionParams['app_id'] = $appAccessData['data']['app_id'];
        $systemAppAccessVersionParams['app_access_id'] = $appAccessData['data']['id'];
        $systemAppAccessVersionParams['merchant_id'] = yii::$app->session['uid'];
        $systemAppAccessVersionParams['type'] = 2;
        $systemAppAccessVersionParams['`key`'] = $params['key'];
        $systemAppAccessData = $systemAppAccessVersionModel->LastFind($systemAppAccessVersionParams);
        if ($systemAppAccessData['status'] != 200) {
            return result(200, "审核提交失败，找不到小程序版本信息");
        }

        //获取授权信息
        $config = $this->getSystemConfig($params['key'], "miniprogram");
        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        $AuthorizerToken = $this->getAuthorizerToken($config['app_id'], $config['refresh_token']);
        //带小程序实现审核
        $access_token = $AuthorizerToken['authorizer_access_token'];
        $url = "https://api.weixin.qq.com/wxa/release?access_token={$access_token}";
        $data = "{}";

        $rs = json_decode(curlPost($url, $data), true);
        if ($rs['errcode'] == 0) {
            $arr['id'] = $systemAppAccessData['data']['id'];
            $arr['status'] = 6;
            $systemAppAccessVersionModel->update($arr);
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['key'];
            $operationRecordData['merchant_id'] = yii::$app->session['uid'];
            $operationRecordData['operation_type'] = '更新';
            $operationRecordData['operation_id'] = $params['key'];
            $operationRecordData['module_name'] = '上传发布';
            $operationRecordModel->do_add($operationRecordData);
            return result(200, "发布成功!", 6);
        } else {
            if ($rs['errcode'] == 42001) {
                $openPlatform = Factory::openPlatform($this->config);
                // 代小程序实现业务
                $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
                $token = $miniProgram->access_token->getToken(true); // 强制重新从微信服务器获取 token
                $miniProgram['access_token']->setToken($token['authorizer_access_token'], 3600);
            }
            return result(500, $rs, 7);
        }
    }

    /**
     * 查询小程序审核状态
     * @param type $id
     * @param type $access_token
     * @return type
     */
    public function actionAuditstatus() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }
        //获取微信配置信息
        $params = $request['params'];
        //获取授权信息
        $config = $this->getSystemConfig($params['key'], "miniprogram");

        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        $AuthorizerToken = $this->getAuthorizerToken($config['app_id'], $config['refresh_token']);
        $access_token = $AuthorizerToken['authorizer_access_token'];
        //应用版本信息
        $appAccessModel = new AppAccessModel();
        $appAccessParams['`key`'] = $params['key'];
        $appAccessData = $appAccessModel->find($appAccessParams);

        $systemAppAccessVersionModel = new SystemAppAccessVersionModel();
        $systemAppAccessVersionParams['app_id'] = $appAccessData['data']['app_id'];
        $systemAppAccessVersionParams['app_access_id'] = $appAccessData['data']['id'];
        $systemAppAccessVersionParams['merchant_id'] = yii::$app->session['uid'];
        $systemAppAccessVersionParams['type'] = 2;
        $systemAppAccessData = $systemAppAccessVersionModel->LastFind();
        if ($systemAppAccessData['status'] != 200) {
            return $systemAppAccessData;
        }
        $url = "https://api.weixin.qq.com/wxa/get_auditstatus?access_token={$access_token}";
        $data['auditid'] = $systemAppAccessData['data']['return_id'];
        $rs = curlPost($url, json_encode($data, JSON_UNESCAPED_UNICODE));
        return result(200, "请求成功", $rs);
    }

    public function downloadImg($url, $app_id = "", $refresh_token = "") {
        try {
            $data = $this->getImage($url);
            $res = $data['res'];
            $data['res'] = json_decode($data['res'], true);
            if (isset($data['res']['errcode']) && $data['res']['errcode'] == 42001) {
                $openPlatform = Factory::openPlatform($this->config);

                // 代小程序实现业务
                $miniProgram = $openPlatform->miniProgram($app_id, $refresh_token);
                $token = $miniProgram->access_token->getToken(true); // 强制重新从微信服务器获取 token
                $miniProgram['access_token']->setToken($token['authorizer_access_token'], 3600);
                return false;
            }
            $path = "./uploads/miniprogram/";
            $type = substr($data['info']['content_type'], 6);
            if($type==".action/json"){
                return false;
            }
            $file_name = "wx_" . time() . uniqid() . "." . $type;
            file_put_contents($path . $file_name, $res);
            return $path . $file_name;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getImage($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $res = curl_exec($ch);
        $rs = curl_getinfo($ch);
        curl_close($ch);

        return array('info' => $rs, 'res' => $res);
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

    /*
     * 获取第三方平台access_token
     * 注意，此值应保存，代码这里没保存
     */
    /*
     * 更新授权小程序的authorizer_access_token
     * @params string $appid : 小程序appid
     * @params string $refresh_token : 小程序authorizer_refresh_token
     * */

    private function getAuthorizerToken($appid, $refresh_token) {
        $openPlatform = Factory::openPlatform($this->config);
        // 代小程序实现业务
        $miniProgram = $openPlatform->miniProgram($appid, $refresh_token);
        $rs = $miniProgram->access_token->getToken();
        return $rs;
    }

    /**
     * 查询商户小程序上传信息
     */
    public function actionMiniprogram() {
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }
        //获取微信配置信息
        $params = $request['params'];

        //查询应用
        $appAccessModel = new AppAccessModel();
        $appAccessParams['`key`'] = $params['key'];
        $appAccessParams['merchant_id'] = yii::$app->session['uid'];
        $appAccessData = $appAccessModel->find($appAccessParams);

        if ($appAccessData['status'] != 200) {
            return result(500, "请求失败");
        }

//        $appModel = new AppModel();
//        $appParams['id'] = $appAccessData['data']['app_id'];
//        $appData = $appModel->find($appParams);
        //商户小程序  获取最后一个上传审核的版本信息概况
        $systemAppAccessVersionModel = new SystemAppAccessVersionModel();
        $systemAppAccessVersionParams['app_id'] = $appAccessData['data']['app_id'];
        $systemAppAccessVersionParams['app_access_id'] = $appAccessData['data']['id'];
        $systemAppAccessVersionParams['merchant_id'] = yii::$app->session['uid'];
        $systemAppAccessVersionParams['type'] = 2;
        $systemAppAccessVersionParams['`key`'] = $params['key'];
        $systemAppAccessData = $systemAppAccessVersionModel->LastFind($systemAppAccessVersionParams);

        //获取第三方平台上传的小程序上传版本信息概况
        $systemAppVersionModel = new SystemAppVersionModel();
        $systemAppVersionParams['app_id'] = $appAccessData['data']['app_id'];
        $systemAppVersionParams['type'] = 2;

        $systemAppVersionData = $systemAppVersionModel->LastFind($systemAppVersionParams);

        $data['adminVersionNumber'] = $systemAppVersionData['data']['number'];
        if ($systemAppAccessData['status'] == 200) {
            $data['merchantVersionNumber'] = $systemAppAccessData['data']['number'];
            $data['merchantVersionStatus'] = $systemAppAccessData['data']['status'];
        } else {
            $data['merchantVersionNumber'] = "";
            $data['merchantVersionStatus'] = -1;
        }

        return result(200, '请求成功', $data);
    }

    public function actionUndocodeaudit(){
        $request = request();
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }
        $params = $request['params'];

        //获取授权信息
        $config = $this->getSystemConfig($params['key'], "miniprogram");
        if ($config == false) {
            return result(500, "未配置微信信息");
        }
        $AuthorizerToken = $this->getAuthorizerToken($config['app_id'], $config['refresh_token']);
        //带小程序实现审核
        $access_token = $AuthorizerToken['authorizer_access_token'];
        $url = "https://api.weixin.qq.com/wxa/undocodeaudit?access_token={$access_token}";
        $res = json_decode(curlGet($url), true);

        if($res['errcode']==0){
            //商户小程序  获取最后一个上传审核的版本信息概况
            //获取第三方平台上传的小程序上传版本信息概况
            $systemAppAccessVersionModel = new SystemAppAccessVersionModel();
            $systemAppAccessVersionParams['`key`'] = $params['key'];
            $systemAppAccessVersionParams['merchant_id'] = yii::$app->session['uid'];
            $systemAppAccessData = $systemAppAccessVersionModel->LastFind($systemAppAccessVersionParams);
            $arr['id'] = $systemAppAccessData['data']['id'];
            $arr['status'] = 4;
            $systemAppAccessVersionModel->update($arr);
            return result(200,"请求成功");
        }else if($res['errcode']==87013){
            return result(500,"已超过限制次数");
        }else if ($res['errcode'] == 42001) {
            $openPlatform = Factory::openPlatform($this->config);
            // 代小程序实现业务
            $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
            $token = $miniProgram->access_token->getToken(true); // 强制重新从微信服务器获取 token
            $miniProgram['access_token']->setToken($token['authorizer_access_token'], 3600);
            return result(500,"请求失败,请稍后重新操作");
        }else{
            return result(500,"请求失败");
        }
    }

    public function getConfig($type, $arr) {
        $model = new SystemWxConfigModel();
        $appId = $arr['appid'];
        $appId = substr($appId, 1);
        if ($type == "miniprogram") {
            $arr['miniprogram_id'] = $appId;
        } else {
            $arr['app_id'] = $appId;
        }
        $rs = $model->find($arr);
        if ($rs['status'] != 200) {
            return false;
        }
        return $rs;
    }

}
