<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\web\MerchantController;
use app\models\merchant\system\MerchantDiyConfigModel;
use app\models\system\SystemDiyConfigModel;

class DiyController extends MerchantController {

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

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => [], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionAll() {
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
            $params['app_id'] = 2;
            $params['field'] = "system_diy_config.*,system_app.name as app_name ";
            $params['join'][] = ['inner join', 'system_app', 'system_app.id = system_diy_config.app_id'];
            //$params['join'][] = ['inner join', 'shop_diy_config', 'system_diy_config.id = shop_diy_config.system_diy_config_id'];
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数      
            $model = new MerchantDiyConfigModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $params['status'] = 1;
            $array = $model->do_select($params);



            $model = new SystemDiyConfigModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['title'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $data['app_id'] = 2;
            $data['field'] = "system_diy_config.*,system_app.name as app_name ";
            $data['join'][] = ['inner join', 'system_app', 'system_app.id = system_diy_config.app_id'];
            //$params['join'][] = ['inner join', 'shop_diy_config', 'system_diy_config.id = shop_diy_config.system_diy_config_id'];
            $system = $model->do_select($data);
            if ($system['status'] != 200) {
                return $system;
            }

            $res = array();
            for ($i = 0; $i < count($system['data']); $i++) {
                $res[$i] = $system['data'][$i];
                $res[$i]['config_type'] = 1;
                if ($array['status'] == 200) {
                    for ($j = 0; $j < count($array['data']); $j++) {
                        if ($system['data'][$i]['id'] == $array['data'][$j]['system_diy_config_id']) {
                            $res[$i]['id'] = $array['data'][$j]['id'];
                            $res[$i]['value'] = $array['data'][$j]['value'];
                            $res[$i]['config_type'] = 0;
                        }
                    }
                }
            }


            return result(200, '请求成功', $res);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantDiyConfigModel();
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
            $model = new MerchantDiyConfigModel();
            $must = ['key', 'system_diy_config_id'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $adminModel = new SystemDiyConfigModel();
            $res = $adminModel->do_one(['id' => $params['system_diy_config_id']]);
            $data['key'] = $params['key'];
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['system_diy_config_id'] = $params['system_diy_config_id'];
            $data['value'] = $params['value'];
            $data['type'] = 2;
            $data['status'] = 1;
            $array = $model->do_add($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new MerchantDiyConfigModel();
            $where['id'] = $id;

            $array = $model->do_update($where, $params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '页面设置';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new MerchantDiyConfigModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
