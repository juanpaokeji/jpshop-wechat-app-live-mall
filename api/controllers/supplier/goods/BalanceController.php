<?php

namespace app\controllers\supplier\goods;

use app\models\shop\BalanceModel;
use app\models\tuan\LeaderModel;
use yii;
use yii\web\SupplierController;
use app\models\system\SystemSubAdminBalanceModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class BalanceController extends SupplierController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\ShopFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//            //  'except' => ['order'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数      
            $model = new SystemSubAdminBalanceModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            // $params['status'] = 1;
            $params['key'] = yii::$app->session['key'];
            $params['sub_admin_id'] = yii::$app->session['sid'];
            //$params['merchant_id'] = yii::$app->session['uid'];

            $userModel = new \app\models\merchant\system\UserModel();
            $userData = $userModel->find(['id' => yii::$app->session['sid']]);
            if ($userData['status'] == 200) {
                $array = $model->do_select($params);
                $array['balance'] = $userData['data']['balance'];
                return $array;
            } else {
                return result(500, '请求失败');
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemSubAdminBalanceModel();
            $params['id'] = $id;
            $params['key'] = yii::$app->session['key'];
            $params['sub_admin_id'] = yii::$app->session['sid'];
            $params['merchant_id'] = yii::$app->session['uid'];
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
            $model = new SystemSubAdminBalanceModel();
            $must = ['realname', 'money', 'pay_number'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $userModel = new \app\models\merchant\system\UserModel();
            $userData = $userModel->find(['id' => yii::$app->session['sid']]);
            if ($userData['data']['balance'] < $params['money']) {
                return result(500, "提现金额大于余额");
            }

            $leader = json_decode($userData['data']['leader'],true);
            if (isset($leader['points'])) {
                $params['remain_money'] = $params['money'] - ($params['money'] * $leader['points'] / 100);
            } else {
                $params['remain_money'] = $params['money'] - ($params['money'] * 1 / 100);
            }
            $params['balance_sn'] = "balance_" . order_sn();
            $params['type'] = 6;
            $params['is_send'] = 1;
            $params['content'] = "余额提现";
            $params['key'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['sub_admin_id'] = yii::$app->session['sid'];
            $array = $model->do_add($params);
            $sql = "update  system_sub_admin set balance  = balance-" . (float)$params['money'] . " where id = " . yii::$app->session['sid'];
            $res = Yii::$app->db->createCommand($sql)->execute();
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
            $model = new SystemSubAdminBalanceModel();
            $where['id'] = $id;
            $where['key'] = yii::$app->session['key'];
            $where['sub_admin_id'] = yii::$app->session['sid'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_update($where, $params);
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
            $model = new MerchantDiyConfigModel();
            $params['id'] = $id;
            $params['key'] = yii::$app->session['key'];
            $params['sub_admin_id'] = yii::$app->session['sid'];
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //佣金明细列表
    public function actionCommission()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new BalanceModel();
            $params['supplier_id'] = yii::$app->session['sid'];
            $array = $model->do_select($params);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


}
