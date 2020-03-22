<?php

namespace app\controllers\forum;

use yii;
use yii\web\ForumController;
use yii\db\Exception;
use EasyWeChat\Factory;
use app\models\forum\UserModel;
use app\models\core\Token;
use app\models\merchant\app\AppAccessModel;
use app\models\forum\ScoreModel;
use app\models\forum\ForumModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class UserController extends ForumController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ForumFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['login', 'callback', 'useraddress', 'user', 'test'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA',
    ];

    /**
     * 用户登陆  微信，小程序
     */
    public function actionLogin() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $config = $this->getSystemConfig($params['key'], $params['type']);
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $con = $this->config;
            $con['oauth'] = array(
                'scopes' => ['snsapi_userinfo'],
                'callback' => "https://api2.juanpao.com/forum/user/callback?key={$params['key']}&type={$params['type']}",
            );
            if ($params['type'] == "wechat") {
                if ($config['type'] == 1) {
                    $app = Factory::officialAccount($con);
                    //微信公众号 手工填写
                    $oauth = $app->oauth;
                    $oauth->redirect()->send();
                } else if ($config['type'] == 2) {
                    //微信公众号 授权 
                    $openPlatform = Factory::openPlatform($con);
                    // 代公众号实现业务
                    $app = $openPlatform->officialAccount($config['app_id'], $config['refresh_token']);
                    $oauth = $app->oauth;
                    $oauth->redirect()->send();
                } else {
                    return result(500, "未配置微信信息");
                }
            } else if ($params['type'] == "miniprogram") {
                $openPlatform = Factory::openPlatform($con);
                // 代小程序实现业务
                $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
                $user = $miniProgram->auth->session($params['code']);
                if (isset($user['errcode'])) {
                    return result(200, "请求失败", $user);
                } else {
                    return result(200, "请求成功", $user);
                }
            } else {
                return result(500, "登陆失败");
            }
        }
    }

    /**
     * 小程序加密数据并查询数据或者新增用户信息
     */
    public function actionUser() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参

            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $rs = $this->decryptData($config['app_id'], $params['sessionKey'], $params['encryptedData'], $params['iv'], $data);
            if ($rs['status'] == 200) {
                $rs = json_decode($rs['data'], true);
            } else {
                return $rs;
            }
            if (!$rs['unionId']) {
                return result(500, "请先关注公众账号");
            } else {
                $merchant_id = $this->getMerchant($params['key']);
                if ($merchant_id != false) {
                    $users = $this->user($rs, $params['key'], $merchant_id);
                    if ($users['status'] == 200) {
                        $jwt = $this->jwt($users['data'], $params['key'], $merchant_id);
                        $this->sign();
                    } else {
                        return result(500, "用户信息失败");
                    }

                    return result(200, "添加成功", $jwt['data']);
                } else {
                    return result(500, "登陆失败,未找到商户信息");
                }
            }
        }
    }

    /**
     * 授权回调
     */
    public function actionCallback() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $config = $this->getSystemConfig($params['key'], $params['type']);
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $con = $this->config;
            $con['oauth'] = array(
                'scopes' => ['snsapi_userinfo'],
                'callback' => "https://api2.juanpao.com/forum/user/callback?key={$params['key']}&type={$params['type']}",
            );
            if ($params['type'] == "wechat") {
                if ($config['type'] == 1) {
                    $app = Factory::officialAccount($config);
                    $oauth = $app->oauth;
                } else if ($config['type'] == 2) {
                    //微信公众号 授权 
                    $openPlatform = Factory::openPlatform($con);
                    // 代公众号实现业务
                    $app = $openPlatform->officialAccount($config['app_id'], $config['refresh_token']);
                    $oauth = $app->oauth;
                } else {
                    return result(500, "未配置微信信息");
                }
            }
            $user = $oauth->user();
            $user = $app->user->get($user->getId());
            if ($user['subscribe'] == 0) {
                return result(500, "请关注我们的公众号！");
            }
            $merchant_id = $this->getMerchant($params['key']);
            if ($merchant_id != false) {
                $users = $this->user($user, $params['key'], $merchant_id);
                if ($users['status'] == 200) {
                    $jwt = $this->jwt($users['data'], $params['key'], $merchant_id);
                    $this->sign();
                    //  echo "<script>location.href='https://api2.juanpao.com/tieba/dist/index.html#/?token={$jwt['data']}'</script>";
                    echo "<script>location.href='https://api2.juanpao.com/newtest/quanzi/index.html#/?token={$jwt['data']}'</script>";
                    die();
                } else {
                    return result(500, "登陆失败");
                }
            } else {
                return result(500, "登陆失败,未找到商户信息");
            }
        }
    }

    public function actionApp() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象

            return result(500, "请求成功", yii::$app->session['key']);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAppinfo() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $app = new AppAccessModel();
            $res = $app->find(['`key`' => yii::$app->session['key']]);
            $userModel = new UserModel();
            $user = $userModel->findall(['`key`' => yii::$app->session['key'], 'merchant_id' => $res['data']['merchant_id']]);
            $res['data']['number'] = $user['count'];

            $sql = "select sum(hits_count)as number from forum_post where `key`='" . yii::$app->session['key'] . "' and merchant_id = " . $res['data']['merchant_id'];
            $array = $userModel->querySql($sql);
            $res['data']['hits_count'] = $array[0]['number'];


            if ($res['status'] == 200) {
                unset($res['data']['id']);
                unset($res['data']['app_id']);
                unset($res['data']['merchant_id']);
                unset($res['data']['combo_id']);
                unset($res['data']['config']);
                unset($res['data']['expire_time']);
                unset($res['data']['shop_category_id']);
                unset($res['data']['type']);
                unset($res['data']['status']);
                unset($res['data']['create_time']);
                unset($res['data']['update_time']);
                unset($res['data']['delete_time']);
            }

            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionJssdk() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $config = $this->getSystemConfig(yii::$app->session['key'], "wechat");
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $openPlatform = Factory::officialAccount($config);
            $url = "https://api2.juanpao.com/newtest/quanzi/index.html";
            $data = "";
            if ($config['type'] == 1) {
                //微信公众号 手工填写  jssdk
                $openPlatform->jssdk->setUrl($url);
                $data = son_decode($openPlatform->jssdk->buildConfig(array('onMenuShareQQ', 'onMenuShareWeibo'), true), true);
            } else if ($config['type'] == 2) {
                //微信公众号 授权 
//                $config['component_verify_ticket'] = getConfig("component_verify_ticket");
                $openPlatform = Factory::openPlatform($this->config);
                // 代公众号实现业务
                $app = $openPlatform->officialAccount($config['app_id'], $config['refresh_token']);
                $app->jssdk->setUrl($url);
                $data = json_decode($app->jssdk->buildConfig(array('onMenuShareQQ', 'onMenuShareWeibo'), true), true);
            } else {
                return result(500, "信息获取失败");
            }
            return result(200, "请求成功", $data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUseraddress() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $longitude = $params['longitude'];
            $latitude = $params['latitude'];
            $url = "https://apis.map.qq.com/ws/geocoder/v1/?location={$latitude},{$longitude}&key=N6CBZ-NIMKQ-IQ55X-GZXLL-C7HDH-NWBNZ&get_poi=0";
            $array = curlGet($url);
            $rs = jsonDecode($array);
            if ($rs['status'] == 0) {
                return result(200, "请求成功", $rs['result']['address']);
            } else {
                return result(500, "请求失败");
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /*
     * 获取用户信息 
     */

    public function actionInfo() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['id'] = yii::$app->session['user_id'];
            $user = new UserModel();
            $userinfo = $user->find($params);
            return $userinfo;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function user($user, $key, $merchant_id) {
        $userModel = new UserModel();
        /**
         * 小程序key 转换统一的微信公众号的key
         */
        if (isset($user['openId'])) {
            $user['openid'] = $user['openId'];
            unset($user['openId']);
        }
        if (isset($user['nickName'])) {
            $user['nickname'] = $user['nickName'];
            unset($user['nickName']);
        }
        if (isset($user['unionId'])) {
            $user['unionid'] = $user['unionId'];
            unset($user['unionId']);
        }
        if (isset($user['union_id'])) {
            $user['unionid'] = $user['union_id'];
            unset($user['union_id']);
        }

        if (isset($user['gender'])) {
            $user['sex'] = $user['gender'];
            unset($user['gender']);
        }
        if (isset($user['avatarUrl'])) {
            $user['headimgurl'] = $user['avatarUrl'];
            unset($user['avatarUrl']);
        }

        $data['union_id'] = $user['unionid'];
        $data['`key`'] = $key;
        $result = $userModel->find($data);
        if ($result['status'] == 200) {
            $data = array(
                'id' => $result['data']['id'],
                'union_id' => $user['unionid'],
                'open_id' => $user['openid'],
                'nickname' => $user['nickname'],
                'merchant_id' => $merchant_id,
                '`key`' => $key,
                'sex' => $user['sex'],
                'city' => $user['city'],
                'province' => $user['province'],
                'avatar' => $user['headimgurl'],
            );
            if ($user['type'] == 1) {
                if (isset($user['openId'])) {
                    $data['wx_open_id'] = $user['openid'];
                }
            }

            if ($user['type'] == 2) {
                if (isset($user['openId'])) {
                    $data['mini_open_id'] = $user['openid'];
                }
            }
            $array = $userModel->update($data);
        } else {
            $data = array(
                'open_id' => $user['openid'],
                'union_id' => $user['unionid'],
                'nickname' => $user['nickname'],
                '`key`' => $key,
                'merchant_id' => $merchant_id,
                'sex' => $user['sex'],
                'city' => $user['city'],
                'province' => $user['province'],
                'avatar' => $user['headimgurl'],
            );
            if ($user['type'] == 1) {
                if (isset($user['openId'])) {
                    $data['wx_open_id'] = $user['openid'];
                }
            }

            if ($user['type'] == 2) {
                if (isset($user['openId'])) {
                    $data['mini_open_id'] = $user['openid'];
                }
            }
            $array = $userModel->add($data);
        }
        return $array;
    }

    public function jwt($user_id, $key, $mid) {
        $payload = [
            'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
            'exp' => $_SERVER['REQUEST_TIME'] + 12 * 60 * 60, //过期时间
            'user_id' => $user_id,
            'key' => $key,
            'merchant_id' => $mid,
        ];
        $tokenClass = new Token(yii::$app->params['JWT_KEY_FORUM']);
        try {
            $token = $tokenClass->encode($payload);
            //返回token
            if ($token) {
                $array = [
                    'status' => 200,
                    'message' => '请求成功',
                    'data' => $token,
                ];
            } else {
                return result(500, 'token生成失败,请再次登录');
            }
        } catch (\Exception $e) {
            return result(500, '内部错误');
        }
        return $array;
    }

    public function decryptData($appid, $sessionKey, $encryptedData, $iv, &$data) {
        if (strlen($sessionKey) != 24) {
            return result(500, "缺少参数sessionKey");
        }
        $aesKey = base64_decode($sessionKey);
        if (strlen($iv) != 24) {
            return result(500, "缺少参数iv");
        }
        $aesIV = base64_decode($iv);
        $aesCipher = base64_decode($encryptedData);
        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
        $dataObj = json_decode($result);
        if ($dataObj == NULL) {
            return result(500, "参数有误");
        }
        if ($dataObj->watermark->appid != $appid) {
            return result(500, "appid不匹配");
        }

        return result(200, "解码成功", $result);
    }

    /**
     * 签到
     */
    public function sign() {

        $params['`key`'] = yii::$app->session['key'];
        $params['merchant_id'] = yii::$app->session['merchant_id'];
        $params['user_id'] = yii::$app->session['user_id'];
        $forumModel = new ForumModel();
        $forum = $forumModel->find($params);

        if ($forum['status'] != 200) {
            return;
        }
        $forumconfig = json_decode($forum['data']['config'], true);
        $scoreModel = new ScoreModel();
        $score['`key`'] = yii::$app->session['key'];
        $score['merchant_id'] = yii::$app->session['merchant_id'];
        $score['user_id'] = yii::$app->session['user_id'];
        $score['type'] = "sign_in";
        $score['source_id'] = 0;
        $scoreModel->score($score, $score['type'], $forumconfig['score']);
    }

}
