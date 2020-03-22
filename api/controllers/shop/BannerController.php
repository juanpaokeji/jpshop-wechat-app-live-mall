<?php

namespace app\controllers\shop;

use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\BannerModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class BannerController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['list'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new BannerModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            //$params['merchant_id'] = yii::$app->session['merchant_id'];
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
