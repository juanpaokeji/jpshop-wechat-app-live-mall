<?php

namespace app\controllers\shop;

use app\models\shop\TuanLeaderModel;
use yii;
use yii\web\ShopController;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class MerchantController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => [], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionMerchant() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $merchantModel = new \app\models\merchant\system\UserModel();
         

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
