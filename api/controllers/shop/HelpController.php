<?php

namespace app\controllers\shop;


use app\models\merchant\system\SystemAppAccessHelpCategoryModel;
use app\models\merchant\system\SystemAppAccessHelpModel;
use app\models\system\SystemHelpCategoryModel;
use app\models\system\SystemHelpModel;
use yii;
use yii\web\ShopController;
use yii\db\Exception;

class HelpController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['list', 'category', 'single'], //指定控制器不应用到哪些动作
            ]
        ];
    }


    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new SystemAppAccessHelpModel();
            $params['key']= 'ccvWPn';
            $array = $model->do_select($params);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionCategory() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemAppAccessHelpCategoryModel();
            $array = $model->do_select(['key'=>'ccvWPn']);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemAppAccessHelpModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }



}
