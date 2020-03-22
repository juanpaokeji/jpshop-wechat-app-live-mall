<?php

namespace app\controllers\score;

use yii;
use yii\web\ShopController;
use yii\db\Exception;
use app\models\score\ScoreGoodsCategoryModel;

class CategoryController extends ShopController {

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
            $model = new ScoreGoodsCategoryModel();
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['key'] = yii::$app->session['key'];
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $array = $model->do_select($params);
            if ($array['status'] != 200) {
                return $array;
            }
            $data = array();
            for ($i = 0; $i < count($array['data']); $i++) {
                if ($array['data'][$i]['parent_id'] == 0) {
                    $data[] = $array['data'][$i];
                }
            }

            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['data'] = array();
                for ($j = 0; $j < count($array['data']); $j++) {
                    if ($data[$i]['id'] == $array['data'][$j]['parent_id']) {
                        $data[$i]['data'][] = $array['data'][$j];
                    }
                }
            }
            return ['status' => 200, 'message' => '请求成功', 'data' => $data, 'count' => count($data)];
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new ScoreGoodsCategoryModel();
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['key'] = yii::$app->session['key'];

            if (isset($params['parent_id'])) {
                if ($params['parent_id'] == -1) {
                    $params['<>'] = ['parent_id', 0];
                    unset($params['parent_id']);
                }
            }
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
            $model = new ScoreGoodsCategoryModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
