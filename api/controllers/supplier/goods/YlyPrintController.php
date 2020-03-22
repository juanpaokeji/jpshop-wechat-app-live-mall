<?php

namespace app\controllers\supplier\goods;

use app\models\merchant\system\UserModel;
use yii;
use yii\web\SupplierController;
use app\models\system\SystemSubAdminBalanceModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */

class YlyPrintController extends SupplierController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new UserModel();
            $params['id'] = yii::$app->session['sid'];
            $array = $model->find($params);
            if ($array['status'] == 200 && !empty($array['data']['store_info'])){
                $array['data']['store_info'] = json_decode($array['data']['store_info'],true);
            }
            if ($array['status'] == 200 && !empty($array['data']['yly_config'])){
                $array['data']['yly_config'] = json_decode($array['data']['yly_config'],true);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(500, "请求方式错误");
        }
        $params = $request['params'];
        $params['id'] = $id;
        $table = new UserModel();
        if(isset($params['yly_config'])){
            $params['yly_config'] = json_encode($params['yly_config'], JSON_UNESCAPED_UNICODE);
        }
        $array = $table->ylyupdate($params);
        return $array;
    }


}
