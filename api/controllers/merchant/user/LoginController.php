<?php

namespace app\controllers\merchant\user;

use app\models\merchant\user\MerchantModel;
use app\models\system\SystemSmsAccessModel;
use yii;
use yii\web\Controller;
use yii\db\Exception;
use app\models\core\TableModel;
use app\models\core\Token;
use yii\filters\VerbFilter;
use EasyWeChat\Factory;

class LoginController extends Controller {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * @inheritdoc
     */

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * 登录接口
     * 地址:/admin/user/login
     * @throws Exception if the model cannot be found
     * @return array|string
     */
    public function actionIndex() {
        //获取请求的账号和密码 username
//        $params = yii::$app->request->bodyParams; //获取所有 POST 过来的参数
//        $code = yii::$app->session->get('__captcha/admin/user/login/captcha');
//        //获取生成的验证码 与用户输入进行比较
//        if ($code != $params['code']) {
//            return result(500, '验证码错误');
//        }
//        $table = new TableModel();
//        //通过 username 获取该用户的 salt
//        $where = [
//            'phone' => $params['name'],
//            'delete_time is null' => null
//        ];
//
//        $res = $table->tableSingle('merchant_user', $where);
//        if (gettype($res) != 'array') {
//            return result(500, '该账号不存在');
//        }
//        if (!$res['status']) {
//            return result(500, '账号被禁用，禁止登录');
//        }
//
//        //当该用户名存在时，通过 post 传来的 password 处理后与数据库密码比较
//        $loginPW = md5($params['password'] . $res['salt']);
//        if ($loginPW != $res['password']) {
//            return result(500, '账号或密码错误');
//        }
//        //获取 token
//
//        $payload = [
//            'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
//            'exp' => $_SERVER['REQUEST_TIME'] + 12 * 60 * 60, //过期时间
//            'uid' => $res['id'],
//            'sid' => 0,
//            'key' => ""
//        ];
//        $tokenClass = new Token(yii::$app->params['JWT_KEY_MERCHANT']);
//        try {
//            $table->tableUpdate('merchant_user', ['last_login_time' => time()], ['id' => $res['id']]);
//            $token = $tokenClass->encode($payload);
//            //返回token
//            if ($token) {
//                $array = [
//                    'status' => 200,
//                    'message' => '请求成功',
//                    'data' => $token,
//                    'name' => $res['phone'],
//                ];
//            } else {
//                return result(500, 'token生成失败,请再次登录');
//            }
//        } catch (\Exception $e) {
//            return result(500, '内部错误');
//        }
//        return $array;
        //请求成功示例 {"status":"200","message":"请求成功","data":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6InN4cy00ZjFnMjNhMTJhYSJ9.eyJpc3MiOiJmcm9tIiwiYXVkIjoianlzIiwianRpIjoic3hzLTRmMWcyM2ExMmFhIiwiaWF0IjoxNTIzMjQzNjQ0LCJleHAiOjE1MjU4MzU2NDQsImlkIjoiMiJ9.E1gpYMfUTmEbN9pLGyYnJSzzleVd-rs29gazMjL5L2Y"}
        //请求失败示例 {"status":"500","message":"该账号不存在"}
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数

            $model = new MerchantModel();
            if (isset($params['wx_open_id'])){
                $data['wx_open_id'] = $params['wx_open_id'];
                $array = $model->findone($data);
                if ($array['status'] == 204){
                    $result['status'] = 200;
                    $result['message'] = '该微信未绑定账户';
                    $result['type'] = 2; //表示关注公众号没有绑定账号
                    return $result;
                }
            } elseif (isset($params['name'])){
                $data['phone'] = $params['name'];
                $array = $model->findone($data);
                //当该用户名存在时，通过 post 传来的 password 处理后与数据库密码比较
                if($array['status']!=200){
                    return result(500, '未找到用户名');
                }
                $loginPW = md5($params['password'] . $array['data']['salt']);
                if ($loginPW != $array['data']['password']) {
                    return result(500, '账号或密码错误');
                }

                // if ($array['status'] == 200 && empty($array['data']['wx_open_id'])){
                //     $result['status'] = 200;
                //     $result['message'] = '该账户未关注公众号';
                //     $result['type'] = 1; //表示已有账户，未关注公众号
                //     return $result;
                // }
                if ($array['status'] == 204){
                    return result(500, '该账户不存在');
                }
            } else {
                return result(500, '参数有误');
            }

            if ($array['status'] == 200){

                if (!$array['data']['status']) {
                    return result(500, '账号被禁用，禁止登录');
                }


                //获取 token

                $payload = [
                    'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
                    'exp' => $_SERVER['REQUEST_TIME'] + 12 * 60 * 60, //过期时间
                    'uid' => $array['data']['id'],
                    'sid' => 0,
                    'key' => ""
                ];
                $tokenClass = new Token(yii::$app->params['JWT_KEY_MERCHANT']);
                try {
                    $model->update(['id' => $array['data']['id'],'last_login_time' => time()]);
                    $token = $tokenClass->encode($payload);
                    //返回token
                    if ($token) {
                        $result = [
                            'status' => 200,
                            'message' => '请求成功',
                            'data' => $token,
                            'name' => $array['data']['phone'],
                        ];
                    } else {
                        return result(500, 'token生成失败,请再次登录');
                    }
                } catch (\Exception $e) {
                    return result(500, '内部错误');
                }
                return $result;
                //请求成功示例 {"status":"200","message":"请求成功","data":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6InN4cy00ZjFnMjNhMTJhYSJ9.eyJpc3MiOiJmcm9tIiwiYXVkIjoianlzIiwianRpIjoic3hzLTRmMWcyM2ExMmFhIiwiaWF0IjoxNTIzMjQzNjQ0LCJleHAiOjE1MjU4MzU2NDQsImlkIjoiMiJ9.E1gpYMfUTmEbN9pLGyYnJSzzleVd-rs29gazMjL5L2Y"}
                //请求失败示例 {"status":"500","message":"该账号不存在"}
            } else {
                return result(500, '该账号不存在');
            }

        } else {
            return result(500, "请求方式错误");
        }


    }

    public function actionLogin() {
        //获取请求的账号和密码 username
        $params = yii::$app->request->bodyParams; //获取所有 POST 过来的参数
//        $code = yii::$app->session->get('__captcha/admin/user/login/captcha');
//        //获取生成的验证码 与用户输入进行比较
//        if ($code != $params['code']) {
//            return result(500, '验证码错误');
//        }
        $table = new TableModel();
        //通过 username 获取该用户的 salt
        $where = [
            'username' => $params['name'],
            'delete_time is null' => null
        ];

        $res = $table->tableSingle('system_sub_admin', $where);
        if (gettype($res) != 'array') {
            return result(500, '该账号不存在');
        }
        if (!$res['status']) {
            return result(500, '账号被禁用，禁止登录');
        }

        //当该用户名存在时，通过 post 传来的 password 处理后与数据库密码比较
        $loginPW = md5($params['password'] . $res['salt']);
        if ($loginPW != $res['password']) {
            return result(500, '账号或密码错误');
        }
        //获取 token

        $payload = [
            'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
            'exp' => $_SERVER['REQUEST_TIME'] + 12 * 60 * 60, //过期时间
            'uid' => $res['merchant_id'],
            'sid' => $res['id'],
            'key' => $res['key']
        ];

        $app = $table->tableSingle('system_app_access', ['`key`' => $res['key'], 'merchant_id' => $res['merchant_id']]);
        $tokenClass = new Token(yii::$app->params['JWT_KEY_MERCHANT']);
        try {
            $token = $tokenClass->encode($payload);
            //返回token
            if ($token) {
                $array = [
                    'status' => 200,
                    'message' => '请求成功',
                    'data' => $token,
                    'name' => $res['username'],
                    'key' => $res['key'],
                    'rule' => $res['type'],
                    'type' => $app['app_id']
                ];
            } else {
                return result(500, 'token生成失败,请再次登录');
            }
        } catch (\Exception $e) {
            return result(500, '内部错误');
        }
        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'maxLength' => 4,
                'minLength' => 4,
            ],
        ];
    }

    //检查用户是否关注公众号
    public function actionCheck() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['wechat_flag'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            // 用户扫码之后若关注或已关注公众号，会将openid缓存在redis5秒后过期
            $openId = getRedis($params['wechat_flag']); //redis过期后为null
//            $model = new MerchantModel();

            if (isset($openId)){
                return result(200, '请求成功', $openId);
            }
            return result(500, '请求失败');
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionBind() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数

            $must = ['type', 'name', 'wx_open_id'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new MerchantModel();
            if ($params['type'] == 1){
                //type=1表示已有账户，未关注公众号
                $data['phone'] = $params['name'];
                $array = $model->findone($data);
                if ($array['status'] != 200){
                    return $array;
                }
                $data['id'] = $array['data']['id'];
                $data['wx_open_id'] = $params['wx_open_id'];
                $res = $model->update($data);
            } elseif ($params['type'] == 2){
                //type=2表示关注公众号没有绑定账号，此处有两种情况1、以前没有账号 2、有账号未绑定
                $smsModel = new SystemSmsAccessModel();
                $data['phone'] = $params['name'];
                $rs = $smsModel->find($data['phone']);
                //验证码
                if ($rs['status'] != 200) {
                    return result(500, "未查询到验证码!");
                }
                if ($rs['data']['code'] != $params['vercode']) {
                    return result(500, "验证码不正确!");
                }

                $data['phone'] = $params['name'];
                $array = $model->findone($data);
                if ($array['status'] == 204){  //以前没有账号
                    $params['salt'] = $this->get_randomstr(32);
                    $params['password'] = $this->get_randomstr(8);  //新用户生成随机8位数密码
                    $addData = [
                        'password' => md5($params['password'] . $params['salt']),
                        'salt' => $params['salt'],
                        'phone' => $params['name'],
                        'status' => 1,
                        'create_time' => time(),
                    ];
                    $res = $model->add($addData);
                } elseif ($array['status'] == 200){  //有账号未绑定
                    if (!empty($array['data']['wx_open_id'])){
                        return result(500, "该账号已绑定微信!");
                    }
                    $data['id'] = $array['data']['id'];
                    $data['wx_open_id'] = $params['wx_open_id'];
                    $res = $model->update($data);
                } else {
                    return $array;
                }
            } else {
                return result(500, '参数有误');
            }

            //绑定成功返回token
            if ($res['status'] == 200){
                //获取 token

                $payload = [
                    'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
                    'exp' => $_SERVER['REQUEST_TIME'] + 12 * 60 * 60, //过期时间
                    'uid' => $array['data']['id'],
                    'sid' => 0,
                    'key' => ""
                ];
                $tokenClass = new Token(yii::$app->params['JWT_KEY_MERCHANT']);
                try {
                    $model->update(['id' => $array['data']['id'],'last_login_time' => time()]);
                    $token = $tokenClass->encode($payload);
                    //返回token
                    if ($token) {
                        $result = [
                            'status' => 200,
                            'message' => '请求成功',
                            'data' => $token,
                            'name' => $array['data']['phone'],
                        ];
                    } else {
                        return result(500, 'token生成失败,请再次登录');
                    }
                } catch (\Exception $e) {
                    return result(500, '内部错误');
                }
                return $result;
                //请求成功示例 {"status":"200","message":"请求成功","data":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6InN4cy00ZjFnMjNhMTJhYSJ9.eyJpc3MiOiJmcm9tIiwiYXVkIjoianlzIiwianRpIjoic3hzLTRmMWcyM2ExMmFhIiwiaWF0IjoxNTIzMjQzNjQ0LCJleHAiOjE1MjU4MzU2NDQsImlkIjoiMiJ9.E1gpYMfUTmEbN9pLGyYnJSzzleVd-rs29gazMjL5L2Y"}
                //请求失败示例 {"status":"500","message":"该账号不存在"}
            } else {
                return $res;
            }


        } else {
            return result(500, "请求方式错误");
        }
    }




}
