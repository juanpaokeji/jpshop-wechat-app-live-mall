<?php

namespace app\controllers\shop;

use app\models\merchant\app\SystemAppAccessModel;
use app\models\shop\GroupOrderModel;
use yii;
use yii\web\ShopController;
use yii\db\Exception;
use EasyWeChat\Factory;
use app\models\shop\UserModel;
use app\models\core\Token;
use app\models\merchant\app\AppAccessModel;
use app\models\core\CosModel;
use app\models\merchant\user\MerchantModel;
use app\models\shop\StorePaymentModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class UserController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['login', 'callback', 'useraddress', 'user', 'address', 'appinfo', 'list', 'open-advertisement'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public $config = [
        'app_id' => '',
        'secret' => '',
        'token' => '',
        'aes_key' => '',
    ];

    /**
     * 用户登陆  微信，小程序
     */
    public function actionLogin()
    {
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
                'callback' => "https://api2.juanpao.com/shop/user/callback?key={$params['key']}&type={$params['type']}",
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

                // $openPlatform = Factory::openPlatform($this->config);
                // 代小程序实现业务
                //    $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
                $miniProgram = Factory::miniProgram($config);
                $user = $miniProgram->auth->session($params['code']);
                if (isset($user['openid'])) {
                    $userModel = new UserModel();
                    $userinfo = $userModel->find(['mini_open_id' => $user['openid']]);
                    if ($userinfo['status'] == 200) {
                        $jwt = $this->jwt($userinfo['data']['id'], $userinfo['data']['key'], $userinfo['data']['merchant_id'], 2);
                        return result(203, "添加成功", $jwt['data']);
                    }
                }
                if (isset($user['errcode'])) {
                    return result(500, "请求失败", $user);
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
    public function actionUser()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $config = $this->getSystemConfig($params['key'], "miniprogram");
            if ($config == false) {
                return result(500, "未配置小程序信息");
            }
            $rs = $this->decryptData($config['app_id'], $params['sessionKey'], $params['encryptedData'], $params['iv'], $data);
            if ($rs['status'] == 200) {
                $rs = json_decode($rs['data'], true);
            } else {
                return $rs;
            }

//            if (!isset($rs['unionId'])) {
//                return result(500, "请先关注公众账号");
//            } else {
            $merchant_id = $this->getMerchant($params['key']);
            if ($merchant_id != false) {
                $rs['type'] = 2;
                $users = $this->user($rs, $params['key'], $merchant_id);
                if ($users['status'] == 200) {
                    $jwt = $this->jwt($users['data'], $params['key'], $merchant_id, 2);
                } else {
                    return $users;
                }
                return result(200, "添加成功", $jwt['data']);
            } else {
                return result(500, "登陆失败,未找到商户信息");
            }

            //     }

        }
    }

//    public function actionPhone() {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取body传参
//            $config = $this->getSystemConfig($params['key'], "miniprogram");
//            if ($config == false) {
//                return result(500, "未配置小程序信息");
//            }
//            //微信公众号 授权
//            $openPlatform = Factory::openPlatform($this->config);
//            // 代公众号实现业务
//            $app = $openPlatform->officialAccount($config['app_id'], $config['refresh_token']);
//            $res = $app->encryptor->decryptData($params['sessionKey'], $params['iv'], $params['encryptedData']);
//            if($res['status']=='success'){
//
//            }
//            return result(200, "请求成功", $res);
//        }
//    }

    /**
     * 授权回调
     */
    public function actionCallback()
    {
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
                'callback' => "https://api2.juanpao.com/shop/user/callback?key={$params['key']}&type={$params['type']}",
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
                $user['type'] = 1;
                $users = $this->user($user, $params['key'], $merchant_id);
                if ($users['status'] == 200) {
                    $jwt = $this->jwt($users['data'], $params['key'], $merchant_id);
                    //  echo "<script>location.href='https://api2.juanpao.com/tieba/dist/index.html#/?token={$jwt['data']}'</script>";
                    echo "<script>location.href='https://api2.juanpao.com/newtest/shop/index.html#/?token={$jwt['data']}&key={$params['key']}'</script>";
                    die();
                } else {
                    return result(500, "登陆失败");
                }
            } else {
                return result(500, "登陆失败,未找到商户信息");
            }
        }
    }

    public function actionJssdk()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $config = $this->getSystemConfig(yii::$app->session['key'], "wechat");
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $openPlatform = Factory::officialAccount($config);
            $url = "https://api2.juanpao.com/newtest/shop/index.html";
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

    public function actionUseraddress()
    {
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

    public function actionInfo()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['id'] = yii::$app->session['user_id'];
            $user = new UserModel();
            $userinfo = $user->find($params);
            if ($userinfo['status'] == 200) {
                if ($userinfo['data']['is_leader'] == 0) {
                    $userinfo['data']['is_leader'] = false;
                } else {
                    $userinfo['data']['is_leader'] = true;
                    $leaderModel = new \app\models\tuan\LeaderModel();
                    $leader = $leaderModel->do_one(['uid' => yii::$app->session['user_id']]);
                    if ($leader['status'] == 200) {
                        $userinfo['data']['state'] = $leader['data']['state'];
                        $userinfo['data']['is_self'] = $leader['data']['is_self'] == 0 ? false : true;
                    }
                }
                $orderModel = new GroupOrderModel();
                $res = $orderModel->one(['user_id' => $params['id']]);
                if ($res['status'] == 200) {
                    $userinfo['data']['name'] = $res['data']['name'];
                    $userinfo['data']['phone'] = $res['data']['phone'];
                }
                $vipModel = new \app\models\merchant\vip\VipConfigModel();
                $vip = $vipModel->one(['key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
                if ($vip['status'] == 200) {
                    $userinfo['data']['is_vip_config'] = true;
                    $userinfo['data']['discount_ratio'] = $vip['data']['discount_ratio'];
                    $vipAccessModel = new \app\models\shop\VipAccessModel();
                    $vipAccess = $vipAccessModel->one(['key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);

                    if ($vipAccess['status'] == 200) {
                        $vipM = new \app\models\merchant\vip\VipModel();
                        $vipd = $vipM->one(['key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'id' => $vipAccess['data']['vip_id']]);
                        if ($vipd['status'] == 200) {
                            $userinfo['data']['vip_name'] = $vipd['data']['name'];
                        }
                    }
                } else {
                    $userinfo['data']['is_vip_config'] = false;
                }
                if ($userinfo['data']['is_vip'] == 1 && $userinfo['data']['vip_validity_time'] <= time()) {
                    $where['id'] = yii::$app->session['user_id'];
                    $where['`key`'] = yii::$app->session['key'];
                    $where['is_vip'] = 0;
                    $user->update($where);
                    $userinfo['data']['is_vip'] = 0;
                }
            }
            return $userinfo;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAddress()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $url = "https://restapi.amap.com/v3/config/district?key=bc55956766e813d3deb1f95e45e97d73&subdistrict=1";
            if (isset($params['keywords'])) {
                $url .= "&keywords=" . $params['keywords'];
            }
            $array = json_decode(curlGet($url), true);
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function user($user, $key, $merchant_id)
    {
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
//        $data['union_id'] = $user['unionid'];
        $data['mini_open_id'] = $user['openid'];
        $data['`key`'] = $key;
        $data['merchant_id'] = $merchant_id;
        $result = $userModel->find($data);

        if ($result['status'] == 200) {
            $data = array(
                'id' => $result['data']['id'],
                //   'union_id' => $user['unionid'],
                'nickname' => $user['nickname'],
                'merchant_id' => $merchant_id,
                '`key`' => $key,
                'sex' => $user['sex'],
                'city' => $user['city'],
                'province' => $user['province'],
                'avatar' => $user['headimgurl'],
                'type' => $user['type'],
                'update_time' => time()
            );
            //1==微信 2=小程序
            if ($user['type'] == 1) {
                $data['wx_open_id'] = $user['openid'];
            }

            if ($user['type'] == 2) {
                $data['mini_open_id'] = $user['openid'];
            }
            $array = $userModel->update($data);
        } else {
            $data = array(
                //  'union_id' => $user['unionid'],
                'nickname' => $user['nickname'],
                '`key`' => $key,
                'merchant_id' => $merchant_id,
                'sex' => $user['sex'],
                'city' => $user['city'],
                'province' => $user['province'],
                'avatar' => $user['headimgurl'],
                'type' => $user['type'],
                'create_time' => time(),
            );
            if ($user['type'] == 1) {
                $data['wx_open_id'] = $user['openid'];
            }

            if ($user['type'] == 2) {
                $data['mini_open_id'] = $user['openid'];
            }
            $array = $userModel->add($data);
        }

        return $array;
    }

    public function jwt($user_id, $key, $mid, $type = 1)
    {
        if ($type == 1) {
            $payload = [
                'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
                'exp' => $_SERVER['REQUEST_TIME'] + 24 * 60 * 60, //过期时间
                'user_id' => $user_id,
                'key' => $key,
                'merchant_id' => $mid,
            ];
        } else {
            $payload = [
                'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
                'exp' => $_SERVER['REQUEST_TIME'] + 72 * 60 * 60, //过期时间
                'user_id' => $user_id,
                'key' => $key,
                'merchant_id' => $mid,
            ];
        }

        $tokenClass = new Token(yii::$app->params['JWT_KEY_SHOP']);
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

    public function decryptData($appid, $sessionKey, $encryptedData, $iv, &$data)
    {
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

    public function actionAppinfo()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参
            $app = new AppAccessModel();
            $res = $app->find(['`key`' => $params['key']]);

            $model = new MerchantModel();
            $merchant = $model->find(['id' => $res['data']['merchant_id']]);

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
                $res['data']['open_advertisement'] = json_decode($res['data']['open_advertisement'], true);
            }

//            if ($merchant['status'] == 200) {
//                $res['data']['phone'] = $merchant['data']['phone'];
//            }
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOpenAdvertisement()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取body传参

            $model = new SystemAppAccessModel();
            $where['field'] = "id,open_advertisement";
            $where['key'] = $params['key'];
            $array = $model->do_one($where);
            if ($array['status'] == 200) {
                $array['data']['open_advertisement'] = json_decode($array['data']['open_advertisement'], true);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /*
     * 小程序二维码
     */

    public function actionQcode()
    {
        $request = request();
        $params = $request['params']; //获取body传参
        //判断请求方式
        if ($request['method'] != 'GET') {
            return result('500', '请求失败');
        }

        $config = $this->getSystemConfig(yii::$app->session['key'], "miniprogram");

        $miniProgram = Factory::miniProgram($config);
        $response = $miniProgram->app_code->getUnlimit(yii::$app->session['key'], ['width' => 280, "page" => $params['path']]);
        $url = "";
        if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

            $filename = $response->saveAs(yii::getAlias('@webroot/') . "/uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/", time() . $config['app_id'] . rand(1000, 9999) . ".png");
            $localRes = "./uploads/qcode/" . date('Y') . "/" . date('m') . "/" . date('d') . "/" . $filename;
            $cos = new CosModel();
            $cosRes = $cos->putObject($localRes);

            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
//                unlink(Yii::getAlias('@webroot/') . $localRes);
            } else {
                unlink(Yii::getAlias('@webroot/') . $localRes);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
        }
        return result(200, '请求成功', $url);
    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new UserModel();
            $data['`key`'] = $params['key'];
            $data['limit'] = 8;
            $data['fields'] = " avatar,create_time,update_time ";
            $array = $model->findall($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionMiniprogram()
    {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $config = $this->getSystemConfig($params['key'], $params['type']);
            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $miniProgram = Factory::miniProgram($config);
            $data = $miniProgram->auth->session($params['code']);

            return result(200, "请求成功", $data);
        } else {
            return result(500, "请求方式错误");
        }


    }

    public function actionPhone()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $config = $this->getSystemConfig(yii::$app->session['key'], 'miniprogram');

            if ($config == false) {
                return result(500, "未配置微信信息");
            }
            $miniProgram = Factory::miniProgram($config);
            $decryptedData = $this->decryptData($config['app_id'], $params['session_key'], $params['encryptedData'], $params['iv'], $data);
            return $decryptedData;
        } else {
            return result(500, "请求方式错误");
        }

    }

    public function actionUsercode()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new UserModel();
            $user = $model->find(['id' => yii::$app->session['user_id']]);
            if ($user['status'] != 200) {
                return result(500, '找不到此用户');
            }
            error_reporting(E_ERROR);
            require_once yii::getAlias('@vendor/wxpay/example/qrcode.php');
            creat_mulu1('uploads/qrcode');
            $qrcode = "./uploads/qrcode/" . time() . rand(1000, 9999) . ".png";
            $order_sn = order_sn();
            $str = 'user_id=' . yii::$app->session['user_id'] . '&order_sn=' . $order_sn . '&merchant_id=' . yii::$app->session['merchant_id'] . '&time=' . time();
            \QRcode::png($str, $qrcode);
            $res['url'] = "http://" . $_SERVER['SERVER_NAME'] . "/api/web/" . $qrcode;
            $res['money'] = $user['data']['recharge_balance'];
            $res['order_sn'] = $order_sn;
            return result(200, '请求成功', $res);
        } else {
            return result(500, "请求方式错误");
        }

    }


    public function actionPayment()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $data['key'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $data['order_sn'] = $params['order_sn'];
            $data['status'] = 1;
            $payMentModel = new StorePaymentModel();
            $array = $payMentModel->do_one($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTest()
    {
        $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    }

    public function actionPaymentList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $data['key'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['user_id'] = yii::$app->session['user_id'];
            $data['status'] = 1;
            $payMentModel = new StorePaymentModel();
            $array = $payMentModel->do_select($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUserOrder()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            //待付款 $a，待发货 $b，待收货 $c
            $orderModel = new GroupOrderModel();
            $a = $orderModel->do_select(['status' => 0,'limit'=>false,'user_id'=>yii::$app->session['user_id']]);
            $b = $orderModel->do_select(['status' => 1,'limit'=>false,'user_id'=>yii::$app->session['user_id']]);
            $c = $orderModel->do_select(['status' => 3,'limit'=>false,'user_id'=>yii::$app->session['user_id']]);

            $result['a'] = $a['status'] == 200 ? count($a['data']) : 0;
            $result['b'] = $b['status'] == 200 ? count($b['data']) : 0;
            $result['c'] = $c['status'] == 200 ? count($c['data']) : 0;
            return result(200,'请求成功',$result);
        } else {
            return result(500, "请求方式错误");
        }
    }


}
