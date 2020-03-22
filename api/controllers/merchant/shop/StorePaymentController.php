<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use app\models\shop\StorePaymentModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\merchant\app\AppAccessModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class StorePaymentController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new StorePaymentModel();
            //$params['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['nickname'])) {
                if ($params['nickname'] != "") {
                    $params['nickname'] = ['like', "{$params['nickname']}"];
                }
                unset($params['nickname']);
            }
            if (isset($params['start_time'])) {
                if ($params['start_time'] != "") {
                    $params['>='] = ['shop_store_payment.create_time', strtotime($params['start_time'])];
                   // $params['<='] = ['shop_store_payment.create_time', $params['start_time']];
                }
               unset($params['start_time']);
            }
            if (isset($params['end_time'])) {
                if ($params['end_time'] != "") {
                    $params['<='] = ['shop_store_payment.create_time', strtotime($params['end_time'])];
                  //  $params['>='] = ['shop_store_payment.create_time', $params['end_time']];
                }
               unset($params['end_time']);
            }
            if (isset($params['order_sn'])) {
                if ($params['order_sn'] == "") {
                  unset($params['order_sn']);
                }
            }
            
            $key = $params['key'];
            unset($params['key']);
            $params['shop_store_payment.merchant_id'] = yii::$app->session['uid'];
            $params['shop_store_payment.key'] = $key;
            $params['field'] = "shop_store_payment.*,shop_user.avatar";
            $params['join'][] = ['left join','shop_user','shop_user.id=shop_store_payment.user_id'];
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
            $model = new StorePaymentModel();
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
            $model = new StorePaymentModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $must = ['name', 'key', 'pic_url'];
            $rs = $this->checkInput($must, $params);
            $params['status'] = 1;

            if ($rs != false) {
                return $rs;
            }
            $array = $model->do_add($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = 'banner';
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
            $model = new StorePaymentModel();
            $where['id'] = $id;
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = $params['key'];
            $array = $model->do_update($where, $params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = 'banner';
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
            $model = new StorePaymentModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = 'banner';
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
            $appAccessModel = new AppAccessModel();
            
            $appAccessInfo = $appAccessModel->find(['`key`' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
           
            if($appAccessInfo['status']==200){
            	$res['id'] = $appAccessInfo['data']['id'];
            	$res['store_payment'] = json_decode($appAccessInfo['data']['store_payment'],true);
            	return result (200,'请求成功',$res);
            }else{
            	return $appAccessInfo;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }
    
    
    public function actionUpdateconfig($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
           // $model = new StorePaymentModel();
            
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            $params['status'] = 1;

            if ($rs != false) {
                return $rs;
            }
            $params['merchant_id'] = yii::$app->session['uid'];
          //  $params['`key`'] = $params['key'];
            $appAccessModel = new AppAccessModel();
            unset($params['key']);
            $params['id'] = $id;
            $params['store_payment'] = json_encode($params['store_payment']);
          //  var_dump($params);die();
            $res = $appAccessModel->update($params);
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }
    

}
