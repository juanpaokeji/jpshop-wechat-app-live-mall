<?php

namespace app\controllers\shop;

use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\shop\CategoryModel;
use app\models\shop\MerchantCategoryModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CategoryController extends ShopController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['list', 'alls', 'category', 'all','lists'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 一级分类
     * @return type
     */
    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['parent_id'] = 0;
            //后台分类
            $merchantCategorymodel = new MerchantCategoryModel();
            $params['status'] = 1;
            $array2 = $merchantCategorymodel->finds($params);
            //商户分类
            $array = array();
            for ($i = 0; $i < count($array2['data']); $i++) {
                $array[] = $array2['data'][$i];
            }
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionCategory() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['parent_id'] = 0;
            $model = new MerchantCategoryModel();
            $params['status'] = 1;
            $array = $model->findall($params);
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 二级分类
     * @return type
     */
    public function actionAlls() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantCategoryModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['parent_id!=0'] = null;
//            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['status'] = 1;
            $array = $model->findall($params);
            if ($array['status'] == 200) {
                $array = array_chunk($array['data'], 8);
                return result(200, "请求成功", $array);
            } else {
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 二级分类
     * @return type
     */
    public function actionAll($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['parent_id'] = $id;
            $merchantCategorymodel = new MerchantCategoryModel();
            $params['`key`'] = $params['key'];
            $params['status'] = 1;
            unset($params['key']);
            unset($params['id']);
            $array = $merchantCategorymodel->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new AttributeModel();
            $params['status'] = 1;
            $params['`key`'] = yii::$app->session['key'];
            $array = $model->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new AttributeModel();

            //设置类目 参数
            $must = ['name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            $array = $model->add($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new AttributeModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($params);
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new AttributeModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionLists() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $merchantCategorymodel = new MerchantCategoryModel();
            $params['`key`'] = $params['key'];
            $params['parent_id!=0'] = null;
            $params['status'] = 1;
            unset($params['key']);
            unset($params['id']);
            $array = $merchantCategorymodel->finds($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
