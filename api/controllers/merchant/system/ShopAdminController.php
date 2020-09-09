<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use app\models\shop\ShopAdminMiniConfigModel;
use app\models\shop\ShopAdminUserModel;
use yii;
use yii\web\MerchantController;


class ShopAdminController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopAdminUserModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['nickname'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopAdminUserModel();
            $where['id'] = $id;
            if (isset($params['supplier_id'])){
                $data['supplier_id'] = $params['supplier_id'];
            }
            if (isset($params['is_admin'])){
                $data['is_admin'] = $params['is_admin'];
            }
            if (!isset($data)){
                return result(500, "参数有误");
            }
            $array = $model->do_update($where,$data);

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
                $operationRecordData['module_name'] = '后台小程序用户';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionConfig(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopAdminMiniConfigModel();
            $where['key'] = $params['key'];
            $array = $model->do_one($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionConfigAdd(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','app_id','secret'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopAdminMiniConfigModel();
            $where['key'] = $params['key'];
            $info = $model->do_one($where);
            if ($info['status'] == 200){
                $data['app_id'] = $params['app_id'];
                $data['secret'] = $params['secret'];
                $array = $model->do_update(['id'=>$info['data']['id']],$data);
            }else{
                $data['key'] = $params['key'];
                $data['merchant_id'] = yii::$app->session['uid'];
                $data['app_id'] = $params['key'];
                $data['secret'] = $params['key'];
                $array = $model->do_add($data);
            }

            if ($array['status'] == 200){
                //添加操作记录
                $id = isset($info['data']['id']) ? $info['data']['id'] : $array['data'];
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
                $operationRecordData['module_name'] = '后台小程序配置';
                $operationRecordModel->do_add($operationRecordData);
            }

            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }

}