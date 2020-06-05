<?php

namespace app\controllers\supplier\goods;

use yii;
use yii\db\Exception;
use yii\web\SupplierController;
use app\models\shop\CategoryModel;
use app\models\shop\MerchantCategoryModel;
use app\models\shop\MerchantsCategoryModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use app\models\admin\system\SystemCosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CategoryController extends SupplierController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantCategoryModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['supplier_id'] = yii::$app->session['sid'];
            $array = $model->findall($params);
            if ($array['status'] != 200) {
                return $array;
            }
            $data = array();
            for ($i = 0; $i < count($array['data']); $i++) {
                if ($array['data'][$i]['parent_id'] == 0) {
                    $data[] = $array['data'][$i];
                }
            }

            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['data'] = array();
                for ($j = 0; $j < count($array['data']); $j++) {
                    if ($data[$i]['id'] == $array['data'][$j]['parent_id']) {
                        $data[$i]['data'][] = $array['data'][$j];
                    }
                }
            }
            

            return ['status' => 200, 'message' => '请求成功', 'data' => $data, 'count' => count($data)];
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 原写法转yii写法，查询商品分组列表
     * @return array|bool
     */
    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['supplier_id'] = yii::$app->session['sid'];
            $array = MerchantsCategoryModel::instance()->get_list();
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionParent() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new MerchantCategoryModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['supplier_id'] = yii::$app->session['sid'];
            $data['fields'] = " id,name ";
            $data['parent_id'] = 0;
            $array = $category->finds($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionCategory() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CategoryModel();
            $params['supplier_id'] = yii::$app->session['sid'];
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $category = new MerchantCategoryModel();
            $params['id'] = $id;
            $params['supplier_id'] = yii::$app->session['sid'];
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
            $model = new MerchantCategoryModel();
            $base = new Base64Model();
            //设置类目 参数
            $must = ['name'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['`key`'] = yii::$app->session['key'];
            $params['supplier_id'] = yii::$app->session['sid'];
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
            $model = new MerchantCategoryModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            $params['supplier_id'] = yii::$app->session['sid'];
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

    public function actionStatus($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new MerchantCategoryModel();
            $data['id'] = $id;
            $data['`key`'] = yii::$app->session['key'];
            $data['supplier_id'] = yii::$app->session['sid'];
            $data['status'] = $params['status'];
            if (!isset($data['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($data);
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
            $model = new MerchantCategoryModel();
            $params['id'] = $id;
            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['supplier_id'] = yii::$app->session['sid'];
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

    /**
     * 商户商城商品分类
     */
    public function actionType() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new CategoryModel();
            $data['fields'] = " id,name,parent_id ";
            $data['supplier_id'] = yii::$app->session['sid'];
            $data['parent_id'] = 0;
            $array = $model->finds($data);
            unset($data['parent_id']);
            $data['parent_id !=0'] = null;

            $list = $model->finds($data);
            if ($list['status'] != 200) {
                return result(204, "查询失败");
            }
            for ($i = 0; $i < count($array['data']); $i++) {
                for ($j = 0; $j < count($list['data']); $j++) {
                    if ($array['data'][$i]['id'] == $list['data'][$j]['parent_id']) {
                        $array['data'][$i]['sub'][] = $list['data'][$j];
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商户商城商户商品分类
     */
    public function actionMerchanttype() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantCategoryModel();
            $data['fields'] = " id,name,parent_id ";
            $data['parent_id'] = 0;
            $data['`key`'] = yii::$app->session['key'];
            $data['supplier_id'] = yii::$app->session['sid'];

            if(isset($params['status'])){
                $data['status'] = $params['status'];
            }

            $array = $model->finds($data);
            if ($array['status'] != 200) {
                return result(204, "查询失败");
            }
            unset($data['parent_id']);
            $data['parent_id !=0'] = null;
            $list = $model->finds($data);
            if ($list['status'] != 200) {
                return result(204, "查询失败");
            }
            for ($i = 0; $i < count($array['data']); $i++) {
                $array['data'][$i]['sub'] = array();
                for ($j = 0; $j < count($list['data']); $j++) {
                    if ($array['data'][$i]['id'] == $list['data'][$j]['parent_id']) {
                        $array['data'][$i]['sub'][] = $list['data'][$j];
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
