<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\system\ShopGroupingModel;
use app\models\merchant\user\MerchantModel;
use app\models\system\SystemAreaModel;
use yii;
use yii\web\MerchantController;


class SystemAreaController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new SystemAreaModel();
            $where['level'] = 1;
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
            $where['limit'] = false;
            $array = $model->do_select($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new SystemAreaModel();
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

            $must = ['level','name','code'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SystemAreaModel();
            $info = $model->do_one(['code'=>$params['code']]);
            if ($info['status'] == 200){
                return result(500, "行政区划代码已存在");
            }
            $data['code'] = $params['code'];
            $data['name'] = $params['name'];
            $data['level'] = $params['level'];
            if (isset($params['parent_id'])){
                $data['parent_id'] = $params['parent_id'];
            }else{
                $data['parent_id'] = '100000';
            }
            $array = $model->do_add($data);
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
                $operationRecordData['module_name'] = '地址库';
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

            $model = new SystemAreaModel();
            $where['id'] = $id;
            $data = [];
            if (isset($params['code'])){
                $data['code'] = $params['code'];
            }
            if (isset($params['name'])){
                $data['name'] = $params['name'];
            }
            if (isset($params['level'])){
                $data['level'] = $params['level'];
            }
            if (isset($params['parent_id'])){
                $data['parent_id'] = $params['parent_id'];
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
                $operationRecordData['module_name'] = '地址库';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdateStatus($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SystemAreaModel();
            $info = $model->do_one(['id'=>$id]);
            if ($info['status'] != 200){
                return result(500, "参数有误!");
            }
            if ($info['data']['level'] == 1){
                $resWhere['parent_id'] = $info['data']['code'];
                $resWhere['limit'] = false;
                $res = $model->do_select($resWhere);
                if ($res['status'] == 200){
                    $where['id'][] = $id;
                    $parentId = [];
                    foreach ($res['data'] as $k=>$v){
                        $where['id'][] = $v['id'];
                        $parentId[] = $v['code'];
                    }
                    $rsWhere['in'] = ['parent_id',$parentId];
                    $rsWhere['limit'] = false;
                    $rs = $model->do_select($rsWhere);
                    if ($rs['status'] == 200){
                        foreach ($rs['data'] as $k=>$v){
                            $where['id'][] = $v['id'];
                        }
                    }
                }else{
                    $where['id'] = $id;
                }
            }elseif ($info['data']['level'] == 2){
                $resWhere['parent_id'] = $info['data']['code'];
                $resWhere['limit'] = false;
                $res = $model->do_select($resWhere);
                if ($res['status'] == 200){
                    $where['id'][] = $id;
                    foreach ($res['data'] as $k=>$v){
                        $where['id'][] = $v['id'];
                    }
                }else{
                    $where['id'] = $id;
                }
            }elseif ($info['data']['level'] == 3){
                $where['id'] = $id;
            }else{
                return result(500, "该地区等级有误!");
            }
            $data['status'] = $params['status'];
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
                $operationRecordData['module_name'] = '地址库';
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

            $must = ['code'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new SystemAreaModel();
            $info = $model->do_one(['parent_id'=>$params['code']]);
            if ($info['status'] == 200){
                return result(500, "该地区下还有子类，不能删除");
            }
            $where['id'] = $id;
            $array = $model->do_delete($where);
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
                $operationRecordData['module_name'] = '地址库';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}