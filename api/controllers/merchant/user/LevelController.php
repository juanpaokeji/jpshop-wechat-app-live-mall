<?php

namespace app\controllers\merchant\user;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\user\LevelModel;

class LevelController extends MerchantController {

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
            $model = new LevelModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            //  $params['status'] = 1;
            $array = $model->do_select($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new LevelModel();
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
            $model = new LevelModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_add($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '团长等级';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new LevelModel();
            $where['id'] = $id;
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = $params['key'];
            $array = $model->do_update($where, $params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '团长等级';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new LevelModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '团长等级';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
