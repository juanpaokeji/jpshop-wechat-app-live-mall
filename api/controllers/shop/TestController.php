<?php

namespace app\controllers\shop;

use app\models\core\TableModel;
use yii;
use yii\web\ShopController;
use yii\db\Exception;


/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TestController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['single', 'clear', 'update'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionSingle() {

    }

    public function actionClear() {
        if (yii::$app->request->isGet){
            reidsAll();
            return result(200,'请求成功');
        }
    }

    public function actionUpdate(){
        $sqlfile = '../../update.sql';
        $dbStr = file_get_contents($sqlfile);
        Yii::$app->db->createCommand($dbStr)->execute();
     //   if($res!=false){
        return result(200,'更新成功');
//        }else{
//            return result(500,'更新失败');
//        }
    }



}
