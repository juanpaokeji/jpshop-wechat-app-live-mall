<?php

namespace app\controllers\admin\app;

use yii;
use yii\web\CommonController;
use yii\db\Exception;
use app\models\admin\app\CategoryModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CategoryController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionIndex() {
//        $uid = $_SESSION['uid'];//获取当前登录的用户 id，如果需要的话
        $request = yii::$app->request; //获取 request 对象
        $method = $request->getMethod(); //获取请求方式 GET POST PUT DELETE
        if ($method == 'GET') {
            $params = $request->get(); //获取地址栏参数
        } else {
            $params = $request->bodyParams; //获取body传参
        }

        $category = new CategoryModel();
        switch ($method) {
            case 'GET':
                if (!isset($params['searchName'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 searchName',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                if ($params['searchName'] == "list") {
                    $array = $category->findall($params);
                } else if ($params['searchName'] == "single") {
                    $array = $category->find($params);
                } else {
                    $array = ['status' => 501, 'message' => '无该 searchName 请求',];
                    return json_encode($array, JSON_UNESCAPED_UNICODE);
                }
                break;
            case 'POST':
                $array = $category->add($params);
                break;
            case 'PUT':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $array = $category->update($params);
                }
                break;
            case 'DELETE':
                if (!isset($params['id'])) {
                    $array = ['status' => 400, 'message' => '缺少参数 id',];
                } else {
                    $array = $category->delete($params);
                }
                break;
            default:
                return json_encode(['status' => 404, 'message' => 'ajax请求类型错误，找不到该请求',], JSON_UNESCAPED_UNICODE);
        }
        return $array;
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new CategoryModel();
            $array = $category->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $category = new CategoryModel();
            $params['id'] = $id;
            $array = $category->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $category = new CategoryModel();
            $array = $category->add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        // return result(200, "请求方式错误");
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $category = new CategoryModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $category->update($params);
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
            $category = new CategoryModel();
            $params['id'] = $id;
            if (!isset($params['id'])) {
                $array = ['status' => 400, 'message' => '缺少参数 id',];
            } else {
                $array = $category->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
