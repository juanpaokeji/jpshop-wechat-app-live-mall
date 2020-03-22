<?php

namespace app\controllers\shop;

use app\models\merchant\app\AppAccessModel;
use Yii;
use yii\db\Exception;
use app\models\core\CosModel;
use app\models\shop\GoodsModel;
use EasyWeChat\Factory;
use yii\web\ShopController;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @return array
 *@throws Exception if the model cannot be found
 */
class QcodeController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['qcode'], //指定控制器不应用到哪些动作
            ]
        ];
    }
    public function actionQcode() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $params['`key`'] = $params['key'];
            $res = $this->getSystemConfig($params['key'],"miniprogram");
            $data['pic'] = getConfig($res['app_id']);

            return result(200, '请求成功', $data);
        } else {
            return result(500, "请求方式错误");
        }
    }



}