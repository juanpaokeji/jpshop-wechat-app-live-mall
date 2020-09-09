<?php

namespace app\controllers\merchant\supplier;

use yii;
use yii\web\MerchantController;
use app\models\system\SystemSubAdminBalanceModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class BalanceController extends MerchantController {

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

    public function actionList() {
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
            $params['system_sub_admin_balance.key'] = $params['key'];
            unset($params['key']);
            $params['system_sub_admin_balance.merchant_id'] = yii::$app->session['uid'];
            $params['system_sub_admin_balance.is_send'] = 1;
            $params['system_sub_admin_balance.type'] = 6;
            $params['field'] = "system_sub_admin_balance.*,system_sub_admin.username,system_sub_admin.leader ";
            $params['join'][] = ['inner join', 'system_sub_admin', 'system_sub_admin.id = system_sub_admin_balance.sub_admin_id'];
            $array = $model->do_select($params);
            if ($array['status'] == 200){
                foreach ($array['data'] as $k=>$v){
                    $leader = json_decode($v['leader'],true);
                    $array['data'][$k]['supplier_name'] = $leader['realname'];
                    unset($array['data'][$k]['leader']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemSubAdminBalanceModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_one($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemSubAdminBalanceModel();
            $where['id'] = $id;
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $data['status'] = $params['status'];
            $array = $model->do_update($where, $data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
