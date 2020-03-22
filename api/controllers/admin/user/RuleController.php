<?php

namespace app\controllers\admin\user;

use yii;
use yii\web\CommonController;
use yii\db\Exception;
use app\models\admin\user\RuleModel;

/**
 * 权限接口控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class RuleController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionIndex() {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = yii::$app->request; //获取 request 对象
        $method = $request->getMethod(); //获取请求方式 GET POST PUT DELETE
        if ($method == 'GET') {
            $params = $request->get(); //获取地址栏参数
        } else {
            $params = $request->bodyParams; //获取body传参
        }
        if (!isset($params['searchName'])) {
            $array = ['status' => 400, 'message' => '缺少参数 searchName',];
            return json_encode($array, JSON_UNESCAPED_UNICODE);
        }
        $rule = new RuleModel();
        switch ($method) {
            case 'GET':
                if ($params['searchName'] == "list") {
                    $array = $rule->findall($params);
                } else if ($params['searchName'] == "single") {
                    $array = $rule->find($params);
                } else if ($params['searchName'] == "menu") {
                    $array = $rule->findmenu();
                } else {
                    $array = ['status' => 501, 'message' => '无该 searchName 请求',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'POST':
                $array = $rule->add($params);
                break;
            case 'PUT':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $array = $rule->update($params);
                }
                break;
            case 'DELETE':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $array = $rule->delete($params);
                }
                break;
            default:
                return json_encode(['status' => 404, 'message' => 'ajax请求类型错误，找不到该请求',], JSON_UNESCAPED_UNICODE);
        }
        return $array;
    }

    public function actionList() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数

        $rule = new RuleModel();
        $array = $rule->findall($params);
        return $array;
    }

    public function actionSingle($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数
        $params['id'] = $id;
        $rule = new RuleModel();
        $array = $rule->find($params);
        return $array;
    }

    public function actionMenu() {
        //  $request = yii::$app->request; //获取 request 对象
        //   $params = $request->get(); //获取地址栏参数

        $rule = new RuleModel();
        $array = $rule->findmenu();
        return $array;
    }

    public function actionAdd() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $rule = new RuleModel();
        $array = $rule->add($params);
        return $array;
    }

    public function actionUpdate($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $rule = new RuleModel();
        $params['id'] = $id;
        if (!isset($params['id'])) {
            $array = ['status' => 400, 'message' => '缺少参数 id',];
        } else {
            $array = $rule->update($params);
        }
        return $array;
    }

    public function actionDelete($id) {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $rule = new RuleModel();
        $params['id'] = $id;
        if (!isset($params['id'])) {
            $array = ['status' => 400, 'message' => '缺少参数 id',];
        } else {
            $array = $rule->delete($params);
        }
        return $array;
    }

    public function actionTest() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $rule = new RuleModel();
        $array = $rule->findmenu();
        return $array;
    }

}
