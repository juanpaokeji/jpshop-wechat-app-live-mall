<?php

namespace app\controllers\admin\user;

use yii;
use yii\web\Controller;
use yii\db\Exception;
use app\models\admin\user\UsersModel;


class UsersController extends Controller {
    /**
     * 登录接口
     */
    public function actionLogin()
    {
        $userModel = new UsersModel(['scenario'=>'login']);
        $request  =request();
        $params=$request['params'];
        $result = $userModel->login($params);
        return result($result['status'],$result['message'],$result['data']);
    }
    public function actions(){
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'maxLength'=>4,
                'minLength'=>4,
            ],
        ];
    }

}
