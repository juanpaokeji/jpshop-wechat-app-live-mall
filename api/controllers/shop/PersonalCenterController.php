<?php

namespace app\controllers\shop;

use app\models\merchant\app\SystemAppAccessModel;
use app\models\shop\ShopPersonalCenterModel;
use yii;
use yii\web\ShopController;

class PersonalCenterController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['list','other'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['type'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopPersonalCenterModel();
            $where['type'] = $params['type'];
            $where['status'] = 1;
            $where['limit'] = false;
            $array = $model->do_select($where);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOther(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SystemAppAccessModel();
            $where['key'] = $params['key'];
            $where['field'] = 'is_show_pick_up_code,is_show_my_leader';
            $array = $model->do_one($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
