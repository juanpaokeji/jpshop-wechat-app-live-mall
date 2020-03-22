<?php

namespace app\controllers\partner\user;

use app\models\merchant\partnerUser\PartnerUserModel;
use yii;
use yii\web\Controller;
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
     * 登录
     * @return array
     */
    public function actionLogin() {
        //获取请求的账号和密码 username
        $params = yii::$app->request->bodyParams; //获取所有 POST 过来的参数
        $model = new PartnerUserModel();
        //通过 username 获取该用户的 salt
        $where = [
            'account' => $params['account'],
        ];
        $res = $model->one($where);
        if ($res['status'] != 200) {
            return result(500, '该账号不存在');
        }
        //当该用户名存在时，通过 post 传来的 password 处理后与数据库密码比较
        $loginPW = md5($params['password'] . $res['data']['salt']);
        if ($loginPW != $res['data']['password']) {
            return result(500, '账号或密码错误');
        }
        //获取 token
        $payload = [
            'iat' => $_SERVER['REQUEST_TIME'], //什么时候签发的
            'exp' => $_SERVER['REQUEST_TIME'] + 12 * 60 * 60, //过期时间
            'm_id' => $res['data']['merchant_id'],
            'partner_id' => $res['data']['id'],
            'key' => $res['data']['key']
        ];
        $tokenClass = new Token(yii::$app->params['JWT_KEY_PARTNER']);
        try {
            $token = $tokenClass->encode($payload);
            //返回token
            if ($token) {
                $array = [
                    'status' => 200,
                    'message' => '请求成功',
                    'data' => $token,
                    'name' => $res['data']['account'],
                    'key' => $res['data']['key'],
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

}
