<?php

namespace app\controllers\shop;

use app\models\merchant\app\AppAccessModel;
use app\models\tuan\ConfigModel;
use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\ShopSuppliersModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class SuppliersController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['img'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopSuppliersModel();
            $must = ['brand', 'mold', 'city', 'brand_type', 'introduce', 'pic_urls', 'realname', 'phone', 'position'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['key'] = yii::$app->session['key'];
            $params['uid'] = yii::$app->session['user_id'];
            //$params['pic_urls'] = json_decode($params['pic_urls'], true);
//            $str = "";
//            for ($i = 0; $i < count($params['pic_urls']); $i++) {
//                if ($i == 0) {
//                    $str = $params['pic_urls'][$i];
//                } else {
//                    $str = $str . "," . $params['pic_urls'][$i];
//                }
//            }
            $array = $model->do_add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionImg(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $data['key']=$params['key'];
            $model  = new ConfigModel();
            $array = $model->do_one(['field'=>'pic_url,create_time,update_time','key'=>$data['key']]);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
