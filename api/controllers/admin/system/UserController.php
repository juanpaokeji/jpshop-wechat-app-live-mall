<?php

namespace app\controllers\admin\system;

use yii;
use yii\web\CommonController;
use app\models\system\SystemVipUserModel;
use app\models\system\SystemAreaModel;

class UserController extends CommonController {

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

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new SystemVipUserModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['or'] = ['or', ['like', 'phone', $params['searchName']], ['like', 'company_name', $params['searchName']], ['like', 'telephone', $params['searchName']],['like', 'qq', $params['searchName']],['like', 'email', $params['searchName']]];
                }
                unset($params['searchName']);
            }
            //  $params['status'] = 1;
            //'shop_user', 'shop_tuan_leader.uid = shop_user.id'
            //('LEFT JOIN', 'post', 'post.user_id = user.id');
            $params['join'][] = ['INNER JOIN', 'merchant_user', 'merchant_user.id=merchant_id'];
            $params['field'] = " system_vip_user.*, merchant_user.phone ";
            if (isset($params['province_code'])) {
                if ($params['province_code'] == "") {
                    unset($params['province_code']);
                }
            }
            if (isset($params['city_code'])) {
                if ($params['city_code'] == "") {
                    unset($params['city_code']);
                }
            }
            $array = $model->do_select($params);
            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $areaModel = new SystemAreaModel();
                    $province = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['province_code']]);
                    $city = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['city_code']]);
                    $area = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['area_code']]);
                    $array['data'][$i]['province'] = $province['data'][0];
                    $array['data'][$i]['city'] = $city['data'][0];
                    $array['data'][$i]['area'] = $area['data'][0];
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
            $model = new SystemVipUserModel();
            $params['id'] = $id;
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
            $model = new SystemVipUserModel();
            $where['id'] = $id;
            $data['status'] = $params['status'];
            $array = $model->do_update($where, $data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemVipUserModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
