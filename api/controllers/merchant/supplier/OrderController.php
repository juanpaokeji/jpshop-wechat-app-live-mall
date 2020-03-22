<?php

namespace app\controllers\merchant\supplier;

use app\models\shop\OrderModel;
use yii;
use yii\web\MerchantController;
use app\models\system\SystemSubAdminBalanceModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class OrderController extends MerchantController
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

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            $model = new OrderModel();
            $params['shop_order_group.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_order_group.merchant_id'] = yii::$app->session['uid'];
            $params['shop_order_group.supplier_id<>0'] = null;
            $array = $model->findAll($params);

            if ($array['status'] == 200) {
                $areaModel = new \app\models\system\SystemAreaModel();
                for ($i = 0; $i < count($array['data']); $i++) {
                    if ($array['data'][$i]['province_code'] != null && $array['data'][$i]['city_code'] != null && $array['data'][$i]['area_code'] != null) {
                        $province = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['province_code']]);
                        $city = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['city_code']]);
                        $area = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['area_code']]);
                        $array['data'][$i]['province'] = $province['data'][0];
                        $array['data'][$i]['city'] = $city['data'][0];
                        $array['data'][$i]['area'] = $area['data'][0];
                    } else {
                        $array['data'][$i]['province'] = "";
                        $array['data'][$i]['city'] = "";
                        $array['data'][$i]['area'] = "";
                    }
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
            $model = new SystemSubAdminBalanceModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_one($params);

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
