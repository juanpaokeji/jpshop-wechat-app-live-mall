<?php

namespace app\controllers\shop;

use yii;
use yii\web\ShopController;
use app\models\system\SystemAreaModel;

class SystemAreaController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new SystemAreaModel();
            $where['level'] = 1;
            $where['status'] = 1;
            $where['limit'] = false;
            $array = $model->do_select($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionFind(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['code'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SystemAreaModel();
            $where['parent_id'] = $params['code'];
            $where['status'] = 1;
            $where['limit'] = false;
            $array = $model->do_select($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
