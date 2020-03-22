<?php

namespace app\controllers\merchant\score;

use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\score\ScoreModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class RuleController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//        public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\ForumFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['list'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $scoreModel = new ScoreModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $scoreModel->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate() {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ScoreModel();
            $data['`key`'] = $params['key'];
            unset($params['key']);
            $data['merchant_id'] = yii::$app->session['uid'];
            foreach ($params as $key => $value) {
                $data['type'] = $key;
                $data['score'] = $value;
                $array = $model->update($data);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ScoreModel();
            //设置类目 参数
            $must = ['name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
