<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use app\models\shop\GoodsAdvanceSaleModel;
use app\models\shop\ShopGoodsModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;


class AdvanceSaleController extends MerchantController
{

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

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsAdvanceSaleModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $array = $model->do_select($params);

            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['goods_info'] = json_decode($array['data'][$i]['goods_info'], true);
                    $array['data'][$i]['preheat_time'] = date('Y-m-d H:i:s', $array['data'][$i]['preheat_time']);
                    $array['data'][$i]['start_time'] = date('Y-m-d H:i:s', $array['data'][$i]['start_time']);
                    $array['data'][$i]['end_time'] = date('Y-m-d H:i:s', $array['data'][$i]['end_time']);
                    $array['data'][$i]['pay_start_time'] = date('Y-m-d H:i:s', $array['data'][$i]['pay_start_time']);
                    $array['data'][$i]['pay_end_time'] = date('Y-m-d H:i:s', $array['data'][$i]['pay_end_time']);
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsAdvanceSaleModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new GoodsAdvanceSaleModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            $params['status'] = 1;
            $params['goods_id'] = $params['goods_info']['id'];
            $params['preheat_time'] = strtotime($params['preheat_time']);
            $params['start_time'] = strtotime($params['start_time']);
            $params['end_time'] = strtotime($params['end_time']);
            $params['pay_start_time'] = strtotime($params['pay_start_time']);
            $params['pay_end_time'] = strtotime($params['pay_end_time']);
            $params['goods_info'] = json_encode($params['goods_info']);
            if ($rs != false) {
                return $rs;
            }
            $array = $model->do_add($params);
            if ($array['status'] == 200) {
                //添加操作记录
                $sql = "update shop_goods set is_advance_sale =1 where id = {$params['goods_id']}";
                Yii::$app->db->createCommand($sql)->execute();
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
                $operationRecordData['module_name'] = 'banner';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsAdvanceSaleModel();
            $where['id'] = $id;
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = $params['key'];

            if (isset($params['start_time'])) {
                $params['start_time'] = strtotime($params['start_time']);
            }
            if (isset($params['end_time'])) {
                $params['end_time'] = strtotime($params['end_time']);
            }
            if (isset($params['pay_start_time'])) {
                $params['pay_start_time'] = strtotime($params['pay_start_time']);
            }
            if (isset($params['pay_end_time'])) {
                $params['pay_end_time'] = strtotime($params['pay_end_time']);
            }
            if (isset($params['preheat_time'])) {
                $params['preheat_time'] = strtotime($params['preheat_time']);
            }
            if (isset($params['goods_info'])) {
                $params['goods_id'] = $params['goods_info']['id'];
                $params['goods_info'] = json_encode($params['goods_info']);
            }

            $array = $model->do_update($where, $params);
            if ($array['status'] == 200) {
                if(isset($params['goods_id'])){
                    $sql = "update shop_goods set is_advance_sale =1 where id = {$params['goods_id']}";
                    Yii::$app->db->createCommand($sql)->execute();
                }

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
                $operationRecordData['module_name'] = 'banner';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsAdvanceSaleModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $one = $model->do_one($params);
            if ($one['data']['status'] != 0) {
                return result(500, "活动已开始不可以删除");
            }
            $array = $model->do_delete($params);
            if ($array['status'] == 200) {

                $sql = "update shop_goods set is_advance_sale =0 where id = {$one['data']['goods_id']}";
                Yii::$app->db->createCommand($sql)->execute();

                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
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
                $operationRecordData['module_name'] = 'banner';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
