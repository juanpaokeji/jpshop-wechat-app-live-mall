<?php

namespace app\controllers\supplier\goods;

use yii;
use yii\web\SupplierController;
use yii\db\Exception;
use app\models\shop\ElectronicsModel;

class ElectronicsController extends SupplierController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['sms', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

//            $must = ['key'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return json_encode($rs, JSON_UNESCAPED_UNICODE);
//            }

            $model = new ElectronicsModel();
            $data['join'][] = ['INNER JOIN ', 'system_express', 'express_id=system_express.id'];
            $data['field'] = " shop_electronics.*,system_express.name as express_name ";
            $data['merchant_id']=yii::$app->session['uid'];
            $data['key'] = yii::$app->session['key'];
            if(isset($params['status'])){
                $data['shop_electronics.status'] = $params['status'];
            }
            $array = $model->do_select($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ElectronicsModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ElectronicsModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ElectronicsModel();
            $where['id'] = $id;
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = yii::$app->session['key'];
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ElectronicsModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
