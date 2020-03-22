<?php

namespace app\controllers\system;

use yii;
use yii\web\ShopController;
use app\models\system\SystemFormModel;
use app\models\shop\UserModel;

class FormController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemFormModel();
            $params['formid'] = json_decode($params['formid'], true);

            $res = $model->do_select([]);
            if ($res['status'] == 500) {
                return $res;
            }
            $formid = "";

            if ($res['status'] == 204) {
                for ($i = 0; $i < count($params['formid']); $i++) {
                    $model = new SystemFormModel();
                    $data['formid'] = $params['formid'][$i];
                    $data['merchant_id'] = yii::$app->session['merchant_id'];
                    $userModel = new UserModel();
                    $user = $userModel->find(['id' => yii::$app->session['user_id']]);
                    $data['status'] = 1;
                    $data['mini_open_id'] = $user['data']['mini_open_id'];
                    $data['key'] = yii::$app->session['key'];
                    $array = $model->do_add($data);
                }
            }
            if ($res['status'] == 200) {
                for ($i = 0; $i < count($params['formid']); $i++) {
                    for ($j = 0; $j < count($res['data']); $j++) {
                        if ($res['data'][$j]['formid'] == $params['formid'][$i]) {
                            unset($params['formid'][$i]);
                        }
                    }
                }
                for ($i = 0; $i < count($params['formid']); $i++) {
                    $model = new SystemFormModel();
                    $data['formid'] = $params['formid'][$i];
                    $data['merchant_id'] = yii::$app->session['merchant_id'];
                    $userModel = new UserModel();
                    $user = $userModel->find(['id' => yii::$app->session['user_id']]);
                    $data['status'] = 1;
                    $data['mini_open_id'] = $user['data']['mini_open_id'];
                    $data['key'] = yii::$app->session['key'];
                    $array = $model->do_add($data);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
