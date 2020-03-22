<?php

namespace app\controllers\admin\system;

use yii;
use yii\web\CommonController;
use app\models\system\SystemDiyConfigModel;

class DiyController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['sms', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new SystemDiyConfigModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['title'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $params['field'] = "system_diy_config.*,system_app.name as app_name ";
            $params['join'][] = ['inner join', 'system_app', 'system_app.id = system_diy_config.app_id'];
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemDiyConfigModel();
            $params['id'] = $id;
            $array = $model->do_one($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemDiyConfigModel();
            $must = ['title', 'key', 'value'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $res = $model->do_one(['key' => $params['key']]);
            if ($res['status'] == 200) {
                return result('500', 'key 已存在');
            }
            $array = $model->do_add($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemDiyConfigModel();
            $where['id'] = $id;
            $res = $model->do_one(['key' => $params['key']]);
            if ($res['status'] == 200) {
                return result('500', 'key 已存在');
            }
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemDiyConfigModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
