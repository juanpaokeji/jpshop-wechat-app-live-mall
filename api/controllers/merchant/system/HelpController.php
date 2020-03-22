<?php

namespace app\controllers\merchant\system;

use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\system\SystemHelpModel;
use app\models\system\SystemHelpCategoryModel;

/**
 * 帮助中心分类
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class HelpController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ForumFilter', //调用过滤器
                //  'only' => ['single'], //指定控制器应用到哪些动作
                'except' => ['list', 'single'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new SystemHelpCategoryModel();
            $type = $model->findall($params);

            $model = new SystemHelpModel();
            $array = $model->findall($params);
            $data = array();
            if ($type['status'] == 200 && $array['status'] == 200) {
                for ($i = 0; $i < count($type['data']); $i++) {
                    //  $type['data']['id'] =         
                    $data[$i]['typeName'] = $type['data'][$i]['name'];

                    for ($j = 0; $j < count($array['data']); $j++) {
                        if ($type['data'][$i]['id'] == $array['data'][$j]['category_id']) {
                            $data[$i]['data'][] = $array['data'][$j];
                        }
                    }
                }
                return result(200, "请求成功", $data);
            } else {
                return result(500, "请求失败");
            }
            return $data;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new SystemHelpModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->find($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
