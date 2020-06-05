<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\system\ShopGroupingModel;
use app\models\merchant\system\YlyPrintTemplateModel;
use yii;
use yii\web\MerchantController;


class ShopGroupingController extends MerchantController {

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

            $model = new ShopGroupingModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $params['field'] = "id,name,status,create_time";
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new ShopGroupingModel();
            $where['field'] = "id,name,status";
            $where['id'] = $id;
            $array = $model->do_one($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','name'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopGroupingModel();
            $data['key'] = $params['key'];
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['name'] = $params['name'];
            $array = $model->do_add($data);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '集团分组';
                $operationRecordModel->do_add($operationRecordData);
            }
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

            $model = new ShopGroupingModel();
            $where['id'] = $id;
            if (isset($params['name'])){
                $data['name'] = $params['name'];
            }
            $data['status'] = $params['status'];
            $array = $model->do_update($where,$data);

            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '集团分组';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id){
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopGroupingModel();
            $where['id'] = $id;
            $array = $model->do_delete($where);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '集团分组';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}