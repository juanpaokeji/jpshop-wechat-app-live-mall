<?php

namespace app\controllers\merchant\shop;

use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\CategoryModel;
use app\models\shop\TestModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TestController extends MerchantController {

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
            $model = new TestModel();

            $params['merchant_id'] = yii::$app->session['uid'];
        
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
            $category = new TestModel();
            $params['id'] = $id;
            $array = $category->one($params);
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
            $params['merchant_id'] = yii::$app->session['uid'];
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
            $model = new MerchantCategoryModel();
//

            $where['id'] = $id;
            $where['merchant_id'] = $params['merchant_id'];
            $where['key'] = $params['key'];
            $where['id'] = $id;
            $array = $model->do_update($where, $params);
            return $array;
//            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new TestModel();
            $params['id'] = $id;
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
