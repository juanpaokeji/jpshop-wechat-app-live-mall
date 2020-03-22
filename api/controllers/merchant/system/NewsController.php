<?php

namespace app\controllers\merchant\system;

use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\system\SystemNewsModel;

/**
 * 帮助中心分类
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class NewsController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ForumFilter', //调用过滤器
                //  'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['list', 'single'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemNewsModel();
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemNewsModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->find($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
