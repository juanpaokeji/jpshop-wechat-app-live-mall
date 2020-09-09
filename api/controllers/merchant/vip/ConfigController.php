<?php

namespace app\controllers\merchant\vip;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use app\models\merchant\vip\VipConfigModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;

/**
 * 会员卡配置 一个应用一个配置
 * @author  wmy
 * Class ConfigController
 * @package app\controllers\merchant\vip
 */
class ConfigController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 查询单条
     * @return array
     */
    public function actionOne() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new VipConfigModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 新增
     * @return array
     */
    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new VipConfigModel();
            //设置类目 参数
            $must = ['discount_ratio', 'voucher_count', 'score_times', 'key', 'voucher_type_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if($params['discount_ratio'] <= 0 || $params['discount_ratio'] > 1){
                return result(500, "优惠折扣在要大于0小于等于1");
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->add($params);

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
                $operationRecordData['module_name'] = '会员卡配置';
                $operationRecordModel->do_add($operationRecordData);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 更新
     * @param $id
     * @return array
     */
    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new VipConfigModel();
            $where['id'] = $id;
            if(!$id){
                return result(400, "缺少参数 id");
            }
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $where['key'] = $params['key'];
            unset($params['key']);
            $where['merchant_id'] = yii::$app->session['uid'];
            if(isset($params['discount_ratio'])){
                if($params['discount_ratio'] <= 0 || $params['discount_ratio'] > 1){
                    return result(500, "优惠折扣在要大于0小于等于1");
                }
            }

            $array = $model->do_update($where,$params);

            if ($array['status'] == 200) {
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $where['key'];
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
                $operationRecordData['module_name'] = '会员卡配置';
                $operationRecordModel->do_add($operationRecordData);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 删除
     * @param $id
     * @return array
     */
    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new VipConfigModel();
            $params['id'] = $id;
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
               return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->do_delete($params);
            }

            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
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
                $operationRecordData['module_name'] = '会员卡配置';
                $operationRecordModel->do_add($operationRecordData);
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
