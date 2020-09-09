<?php
namespace app\controllers\shop;

use app\models\shop\ShopSubscribeMessageNumModel;
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

    //订阅消息授权后增加次数
    public function actionAdd(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['template_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['template_id'] = explode(',',$params['template_id']);

            $model = new ShopSubscribeMessageNumModel();
            $where['user_id'] = yii::$app->session['user_id'];
            foreach ($params['template_id'] as $k=>$v){
                $where['template_id'] = $v;
                $info = $model->do_one($where);
                if ($info['status'] == 200){
                    $data['num'] = $info['data']['num'] + 1;
                    $model->do_update($where,$data);
                }else{
                    $model = new ShopSubscribeMessageNumModel();
                    $data['key'] = yii::$app->session['key'];
                    $data['merchant_id'] = yii::$app->session['merchant_id'];
                    $data['user_id'] = yii::$app->session['user_id'];
                    $data['template_id'] = $v;
                    $data['num'] = 1;
                    $model->do_add($data);
                }
            }
            return result(200, "请求成功");
        } else {
            return result(500, "请求方式错误");
        }
    }

}
