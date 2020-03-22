<?php

namespace app\controllers\admin\system;

use yii;
use yii\web\CommonController;
use app\models\system\SystemMiniTemplateModel;
use EasyWeChat\Factory;

class TemplateController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['list', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }
    public $config = [
        'app_id' => 'wx8df3a6f4a4f9ec54',
        'secret' => '7188287cd30aa902d5933654fed60559',
        'token' => 'juanPao',
        'aes_key' => '9ILejPm7rpu5kJykkY13oHMO80bYJkNbQfCvL3otaWA'
    ];

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new SystemMiniTemplateModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['system_mini_template.name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $params['join'][] = ['INNER JOIN ', 'system_app', 'app_id=system_app.id'];
            $params['field'] = " system_mini_template.*,system_app.name as app_name ";
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
            $model = new SystemMiniTemplateModel();
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
            $model = new SystemMiniTemplateModel();
            $must = ['name', 'purpose', 'app_id', 'keyword_list_id', 'keyword_list_name', 'keyword_list'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['keyword_list'] = json_encode($params['keyword_list'], JSON_UNESCAPED_UNICODE);
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
            $model = new SystemMiniTemplateModel();
            $where['id'] = $id;
            $params['keyword_list'] = json_encode($params['keyword_list'], JSON_UNESCAPED_UNICODE);
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
            $model = new SystemMiniTemplateModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTemp() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemMiniTemplateModel();
            $must = ['keyword_list_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $array = $model->do_one(['keyword_list_id' => $params['keyword_list_id']]);
            if ($array['status'] == 200) {
                $array['data']['keyword_list'] = json_decode($array['data']['keyword_list'], true);
            }
            if ($array['status'] == 204) {
                $openPlatform = Factory::openPlatform($this->config);
                // 代小程序实现业务
                $config = $this->getSystemConfig('ccvWPn', "miniprogram");
                if ($config == false) {
                    return result(500, "未配置微信信息");
                }
                $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
                $list = $miniProgram->template_message->get($params['keyword_list_id']);
                $array = array(
                    'status' => 200,
                    'message' => '请求成功',
                    'data' => $list
                );
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
