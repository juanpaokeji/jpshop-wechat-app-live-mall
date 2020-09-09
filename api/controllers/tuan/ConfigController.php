<?php

namespace app\controllers\tuan;

use app\models\merchant\system\ShopSolitaireModel;
use yii;
use yii\web\ShopController;
use yii\db\Exception;
use app\models\tuan\ConfigModel;

class ConfigController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['single'], //指定控制器不应用到哪些动作
            ]
        ];
    }
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
           // $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['key'] = $params['key'];
//            $data['merchant_id'] = 108;
//           $data['key'] = 'jqXkVh';
            $array = $model->do_one($data);
            if ($array['status'] == 200) {
                $time = date("Y-m-d", time());
                if($array['data']['is_open']==1){
                    if((int)$array['data']['open_time']<(int)$array['data']['close_time']){
                        if ($array['data']['open_time'] + strtotime($time . " 00:00:00") <= time() && $array['data']['close_time'] + strtotime($time . " 00:00:00") >= time()) {
                            $array['data']['is_bool'] = false;
                        } else {
                            $array['data']['is_bool'] = true;
                        }
                    }else{

                        if ($array['data']['open_time'] + strtotime($time . " 00:00:00") <= time() && $array['data']['close_time'] + strtotime($time . " 00:00:00")+86400 >= time()) {
                            $array['data']['is_bool'] = false;
                        } else {
                            $array['data']['is_bool'] = true;
                        }
                    }
                }else{
                    $array['data']['is_bool'] = true;
                }


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

            $must = ['is_open', 'open_time', 'close_time', 'close_pic_url', 'is_express', 'is_site', 'is_tuan_express', 'min_withdraw_money', 'withdraw_fee_ratio', 'commission_leader_ratio', 'leader_range'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }


            if ($array['status'] == 204) {
                $params['merchant_id'] = yii::$app->session['uid'];
                $array = $model->do_add($params);
            } else if ($array['status'] == 200) {
                $where['id'] = $array['data']['id'];
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['key'] = $params['key'];
                $array = $model->do_update($where, $params);
            } else {
                return result(500, "请求失败");
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
////
//            $where['id'] = $id;
//            $where['merchant_id'] = yii::$app->session['uid'];
//            $where['key'] = $params['key'];
//            if ($params['status'] == 1) {
//                $data['status'] = 1;
//            } else {
//                $data['status'] = 0;
//            }
//
//            $array = $model->do_update($where, $data);
//            return $array;
////            }
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
