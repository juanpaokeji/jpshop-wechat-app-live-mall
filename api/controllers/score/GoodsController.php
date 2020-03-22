<?php

namespace app\controllers\score;

use yii;
use yii\web\ShopController;
use yii\db\Exception;
use app\models\score\ScoreGoodsModel;
use app\models\score\ScoreGoodsOrderModel;

class GoodsController extends ShopController {

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
            $model = new ScoreGoodsModel();
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['key'] = yii::$app->session['key'];
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            if(isset($params['category_id']) && !empty($params['category_id'])){
                $data['category_id'] = $params['category_id'];
            }
            $array = $model->do_select($data);

            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['pic_urls'] = array_filter(explode(",", $array['data'][$i]['pic_urls']));
                    $orderModel = new ScoreGoodsOrderModel();
                    $orders = $orderModel->do_select(['score_goods_id' => $array['data'][$i]['id']]);
                    if ($orders['status'] == 200) {
                        $array['data'][$i]['sale'] = count($orders['data']);
                    } else {
                        $array['data'][$i]['sale'] = 0;
                    }
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
            $model = new ScoreGoodsModel();
            $data['id'] = $id;
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            // $data['user_id'] = yii::$app->session['user_id'];
            $data['key'] = yii::$app->session['key'];

            $array = $model->do_one($data);

            $orderModel = new ScoreGoodsOrderModel();
            $orders = $orderModel->do_select(['score_goods_id' => $id]);
            if ($orders['status'] == 200) {
                $array['data']['sale'] = count($orders['data']);
            } else {
                $array['data']['sale'] = 0;
            }
            if ($array['status'] == 200) {
                $array['data']['pic_urls'] = array_filter(explode(",", $array['data']['pic_urls']));
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
