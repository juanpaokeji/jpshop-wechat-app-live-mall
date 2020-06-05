<?php
namespace app\controllers\shop;

use Yii;
use yii\web\ShopController;
use app\models\system\SystemMerchantMiniSubscribeTemplateModel;

class SubscribeTemplateController extends ShopController{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['list'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $subscribeModel = new SystemMerchantMiniSubscribeTemplateModel();
            $subscribeWhere['key'] = $params['key'];
            $subscribeWhere['limit'] = false;
            $list = $subscribeModel->do_select($subscribeWhere);
            if ($list['status'] == 200){
                foreach ($list['data'] as $k=>$v){
                    $array[$v['template_purpose']][] = $v['name'];
                    $array[$v['template_purpose']][] = $v['template_id'];
                }
                return result(200, "请求成功",$array);
            }else{
                return $list;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }


}
