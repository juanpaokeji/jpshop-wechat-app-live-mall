<?php

namespace app\controllers\admin\shop;

use yii;
use yii\db\Exception;
use yii\web\CommonController;
use app\models\shop\CategoryModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

header('Access-Control-Allow-Headers:Access-Token');
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE');

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
//    public function actionList() {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数
//            $model = new PostModel();
//            $params['`key`'] = yii::$app->session['key'];
//            $params['merchant_id'] = yii::$app->session['merchant_id'];
//            $params['user_id'] = yii::$app->session['user_id'];
//            $array = $model->findall($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

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

    public function actionParent() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new CategoryModel();
            $data['fields'] = " id,name ";
            $data['parent_id'] = 0;
            $array = $category->finds($data);
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
            $base = new Base64Model();
            $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/shop/category");
            $cos = new CosModel();
            $cosRes = $cos->putObject($params['pic_url']);
            if ($cosRes['status'] == '200') {
                $url = $cosRes['data'];
            } else {
                unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
            }
            $params['pic_url'] = $url;
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
            $base = new Base64Model();
            $params['id'] = $id;
            if (isset($params['pic_url'])) {
                if ($params['pic_url'] != "") {
                    $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/shop/category");
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($params['pic_url']);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                    $params['pic_url'] = $url;
                }
            }

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
