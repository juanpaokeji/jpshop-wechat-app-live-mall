<?php

namespace app\controllers\merchant\tuan;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\tuan\ConfigModel;
use app\models\core\CosModel;
use app\models\core\Base64Model;

class ConfigController extends MerchantController {

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
//    public function actionList() {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
//            $model = new ConfigModel();
//            $params['merchant_id'] = yii::$app->session['uid'];
//          
//            $array = $model->do_select($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ConfigModel();
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['key'] = $params['key'];
            $array = $model->do_one($data);
            if ($array['status'] == 200) {
                $appAccessModel = new AppAccessModel();
                $appAccessInfo = $appAccessModel->find(['`key`' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
                if ($appAccessInfo['status'] == 200){
                    $array['data']['is_open'] = $appAccessInfo['data']['group_buying'];
                } else {
                    return $appAccessInfo;
                }

                $array['data']['open_time'] = secToTime($array['data']['open_time']);
                $array['data']['close_time'] = secToTime($array['data']['close_time']);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionConfig() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ConfigModel();
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['key'] = $params['key'];
            $array = $model->do_one($data);

            $must = ['is_open', 'open_time', 'close_time', 'banner_pic_url', 'is_express', 'is_site', 'is_tuan_express', 'min_withdraw_money', 'withdraw_fee_ratio', 'leader_range'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['commission_leader_ratio'] = isset($params['commission_leader_ratio'])?$params['commission_leader_ratio']:0;
            $open_time = explode(":", $params['open_time']);
            $close_time = explode(":", $params['close_time']);
            $params['open_time'] = ((int) $open_time[0] * 3600) + ((int) $open_time[1] * 60) + ((int) $open_time[2]);
            $params['close_time'] = ((int) $close_time[0] * 3600) + ((int) $close_time[1] * 60) + ((int) $close_time[2]);
            $params['status'] = 1;
            if ($array['status'] == 204) {
                $params['merchant_id'] = yii::$app->session['uid'];
                $params['withdraw_fee_ratio'] = $params['withdraw_fee_ratio'];
                //团购开关
                $appAccessModel = new AppAccessModel();
                $appAccessWhere['`key`'] = $params['key'];
                $appAccessWhere['merchant_id'] = $data['merchant_id'];
                $appAccessWhere['group_buying'] = $params['is_open'];
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $array = $model->do_add($params);
                    $appAccessModel->update($appAccessWhere);
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    return result(500, '数据库操作失败');
                }
            } else if ($array['status'] == 200) {
                $where['id'] = $array['data']['id'];
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['key'] = $params['key'];
                $params['withdraw_fee_ratio'] = $params['withdraw_fee_ratio'];
                //团购开关
                $appAccessModel = new AppAccessModel();
                $appAccessWhere['`key`'] = $params['key'];
                $appAccessWhere['merchant_id'] = $data['merchant_id'];
                $appAccessWhere['group_buying'] = $params['is_open'];

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $array = $model->do_update($where, $params);
                    $appAccessModel->update($appAccessWhere);
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    return result(500, '数据库操作失败');
                }
            } else {
              return  result(500, "请求失败");
            }
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $params['key'];
                $operationRecordData['module_name'] = '团购配置';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionAdd() {
//        if (yii::$app->request->isPost) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new UserModel();
//            $params['merchant_id'] = yii::$app->session['uid'];
//            $array = $model->do_add($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//    public function actionUpdate($id) {
//        if (yii::$app->request->isPut) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new UserModel();
//
//            $where['id'] = $id;
//            $where['merchant_id'] = yii::$app->session['uid'];
//            $where['key'] = $params['key'];
//            $array = $model->do_update($where, $params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

//    public function actionDelete($id) {
//        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new UserModel();
//            $params['id'] = $id;
//            $params['merchant_id'] = yii::$app->session['uid'];
//            $array = $model->do_delete($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
}
