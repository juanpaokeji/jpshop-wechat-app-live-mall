<?php

namespace app\controllers\merchant\score;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\score\ScoreGoodsOrderModel;

class OrderController extends MerchantController {

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
            $model = new ScoreGoodsOrderModel();
            $params['shop_score_order.merchant_id'] = yii::$app->session['uid'];
            if (isset($params['goods_name'])) {
                if ($params['goods_name'] != "") {
                    $params['goods_name'] = trim($params['goods_name']);
                    $params["shop_score_order.name"] = ['like', "{$params['goods_name']}"];
                }
                unset($params['goods_name']);
            }
            if (isset($params['user_id'])) {
                if ($params['user_id'] != "") {
                    $params['user_id'] = trim($params['user_id']);
                    $params["shop_score_order.user_id"] = $params['user_id'];
                }
                unset($params['user_id']);
            }
            if (isset($params['status'])) {
                if ($params['status'] != "") {
                    $params['status'] = trim($params['status']);
                    $params["shop_score_order.status"] = $params['status'];
                }
                unset($params['status']);
            }
            if (isset($params['key'])) {
                if ($params['key'] != "") {
                    $params['key'] = trim($params['key']);
                    $params["shop_score_order.key"] = $params['key'];
                }
                unset($params['key']);
            }

            if (isset($params['start_time'])) {
                if ($params['start_time'] != "") {
                    $time = strtotime(str_replace("+", " ", $params['start_time']));
                    $params[">="] = ['shop_score_order.create_time', $time];
                }
                unset($params['start_time']);
            }
            if (isset($params['end_time'])) {
                if ($params['end_time'] != "") {
                    $time = strtotime(str_replace("+", " ", $params['end_time']));
                    $params["<="] = ['shop_score_order.create_time', $time];
                }
                unset($params['end_time']);
            }
            if (isset($params['searchNameType'])) {
                if ($params['searchNameType'] != "") {
                    if ($params['searchName'] != "") {
                        if ($params['searchNameType'] == 1) {
                            $params['shop_score_order.order_sn'] = trim($params['searchName']);
                        }
                        if ($params['searchNameType'] == 2) {
                            $name = trim($params['searchName']);
                            $params["shop_user_contact.name"] = ['like', "{$name}"];
                        }
                        if ($params['searchNameType'] == 3) {
                            $params['shop_user_contact.phone'] = trim($params['searchName']);
                        }
                    }
                }
                unset($params['searchNameType']);
                unset($params['searchName']);
            }
            $params['field'] = "shop_score_order.*,shop_user_contact.name as content_name,phone,province,city,area,street,address";
            $params['join'][] = ['inner join', 'shop_user_contact', 'shop_user_contact.id = shop_score_order.user_contact_id'];
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
            $model = new ScoreGoodsOrderModel();
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
            $model = new ScoreGoodsOrderModel();
            $where['id'] = $id;
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = $params['key'];
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ScoreGoodsOrderModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
