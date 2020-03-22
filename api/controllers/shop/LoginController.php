<?php

namespace app\controllers\shop;

use yii;
use yii\web\Controller;
use yii\db\Exception;
use app\models\core\TableModel;
use app\models\core\Token;
use yii\filters\VerbFilter;


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
        $params = yii::$app->request->bodyParams; //获取所有 POST 过来的参数
//        $code = yii::$app->session->get('__captcha/admin/user/login/captcha');
//        //获取生成的验证码 与用户输入进行比较
//        if ($code != $params['code']) {
//            return result(500, '验证码错误');
//        }
        $table = new TableModel();
        //通过 username 获取该用户的 salt
        $where = [
            '`name`' => $params['name'],
            'delete_time is null' => null
        ];
        $res = $table->tableSingle('merchant_user', $where);
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
            'uid' => $res['id']
        ];
        $tokenClass = new Token(yii::$app->params['JWT_KEY_MERCHANT']);
        try {
            $token = $tokenClass->encode($payload);
            //返回token
            if ($token) {
                $array = [
                    'status' => 200,
                    'message' => '请求成功',
                    'data' => $token,
                    'name' => $res['name'],
                ];
            } else {
                return result(500, 'token生成失败,请再次登录');
            }
        } catch (\Exception $e) {
            return result(500, '内部错误');
        }
        return $array;
        //请求成功示例 {"status":"200","message":"请求成功","data":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6InN4cy00ZjFnMjNhMTJhYSJ9.eyJpc3MiOiJmcm9tIiwiYXVkIjoianlzIiwianRpIjoic3hzLTRmMWcyM2ExMmFhIiwiaWF0IjoxNTIzMjQzNjQ0LCJleHAiOjE1MjU4MzU2NDQsImlkIjoiMiJ9.E1gpYMfUTmEbN9pLGyYnJSzzleVd-rs29gazMjL5L2Y"}
        //请求失败示例 {"status":"500","message":"该账号不存在"}
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

}
