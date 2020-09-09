<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\user\MerchantModel;
use app\models\shop\BalanceAccessModel;
use Yii;
use yii\web\MerchantController;
use app\models\shop\ShopUserModel;
use app\models\merchant\system\OperationRecordModel;

class RechargeController extends MerchantController
{
    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new ShopUserModel();
            $params['field'] = 'id,avatar,nickname,name,phone,recharge_balance,score';
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['or'] = ['or',['like', 'nickname', $params['searchName']],['like', 'name', $params['searchName']],['like', 'phone', $params['searchName']]];
                }
                unset($params['searchName']);
            }
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

            $model = new ShopUserModel();
            $where['field'] = 'id,avatar,nickname,name,phone,recharge_balance,score';
            $where['id'] = $id;
            $array = $model->do_one($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','type','num'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new ShopUserModel();
            $where['id'] = $id;
            $info = $model->do_one($where);
            if ($info['status'] != 200){
                return result(204, "未查询到会员信息");
            }
            if ($params['type'] == 1){ //充值余额
                $data['recharge_balance'] = $params['num'] + $info['data']['recharge_balance'];
                $accessData['recharge_type'] = 1;
            } elseif ($params['type'] == 2){ //充值积分
                $data['score'] = $params['num'] + $info['data']['score'];
                $accessData['recharge_type'] = 2;
            } else {
                return result(500, "充值类型有误");
            }
            $array = $model->do_update($where, $data);
            if ($array['status'] == 200){
                //充值记录
                $accessModel = new BalanceAccessModel();
                $accessData['key'] = $params['key'];
                $accessData['merchant_id'] = yii::$app->session['uid'];
                $accessData['user_id'] = $id;
                $accessData['money'] = $params['num'];
                $accessData['remain_money'] = $params['num'];
                $accessData['pay_type'] = 6;
                $accessData['pay_sn'] = '线下支付';
                $accessData['transaction_id'] = '线下支付';
                $accessData['status'] = 1;
                $accessModel->add($accessData);
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
                $operationRecordData['module_name'] = '充值';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


}