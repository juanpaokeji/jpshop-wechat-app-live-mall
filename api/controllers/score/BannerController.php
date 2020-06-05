<?php

namespace app\controllers\score;

use yii;
use yii\web\ShopController;
use yii\db\Exception;
use app\models\score\ScoreBannerModel;

class BannerController extends ShopController {

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
            $model = new ScoreBannerModel();
            $data['merchant_id'] = yii::$app->session['merchant_id'];
         //   $data['user_id'] = yii::$app->session['user_id'];
            $data['key'] = yii::$app->session['key'];
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $data['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $array = $model->do_select($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
