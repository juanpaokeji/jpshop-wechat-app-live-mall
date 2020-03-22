<?php

namespace app\controllers\merchant\system;

use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\system\SystemAppVersionModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class VersionController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemAppVersionModel();
            $params['fields'] = " id,title,simple_info,content,number,status,create_time,update_time,delete_time";
            $array = $model->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


    public function actionreballk(){

    }

}
