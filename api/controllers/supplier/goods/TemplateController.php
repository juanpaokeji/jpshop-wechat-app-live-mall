<?php

namespace app\controllers\supplier\goods;

use yii;
use yii\db\Exception;
use yii\web\SupplierController;
use app\models\shop\ShopExpressTemplateModel;
use app\models\shop\ShopExpressTemplateDetailsModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TemplateController extends SupplierController {

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
            $model = new ShopExpressTemplateModel();
//            if (isset($params['key'])) {
//                $params['`key`'] = $params['key'];
//                unset($params['key']);
//            }
//            $params['shop_express.`key`'] = $params['key'];
//            unset($params['key']);
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['uid'];
            // $params['fields'] ="";
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //快递公司列表
    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ShopExpressTemplateModel();
            //$params['status'] = 1;
            $params['shop_express_template.`key`'] = $params['key'];
            unset($params['key']);
            $params['join'] = " inner join shop_express_template_details on shop_express_template.id = shop_express_template_details.shop_express_template_id ";
            $params['shop_express_template.merchant_id'] = yii::$app->session['uid'];
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
            $category = new ShopExpressTemplateModel();
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

            $model = new ShopExpressTemplateModel();
            //设置类目 参数
            $must = ['names'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['`key`'] = $params['key'];
            $templateData['`key`'] = $params['key'];
            $data['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['status'] = 1;
            $temp = $model->find($templateData); //模板

            $status = $temp['status'] == 200 ? 0 : 1;

            $transaction = yii::$app->db->beginTransaction();
            $templateModel = new ShopExpressTemplateDetailsModel();
            try {
                $templateData['merchant_id'] = yii::$app->session['uid'];
                $templateData['name'] = $params['name'];
                $templateData['type'] = $params['type'];
                $templateData['status'] = $status;
                $list = $model->add($templateData); //模板
                $data['merchant_id'] = yii::$app->session['uid'];
                //模板详情信息
                for ($i = 0; $i < count($params['names']); $i++) {
                    $data['shop_express_template_id'] = $list['data'];
                    $data['names'] = $params['names'][$i];
                    $data['first_num'] = $params['first_num'][$i];
                    $data['first_price'] = $params['first_price'][$i];
                    $data['expand_num'] = $params['expand_num'][$i];
                    $data['expand_price'] = $params['expand_price'][$i];
                    $data['status'] = 1;
                    $array = $templateModel->add($data);
                }
                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                return result(200, "新增成功");
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "新增失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopExpressTemplateModel();
            $params['id'] = $id;
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                $templateData['`key`'] = $params['key'];
                $data['`key`'] = $params['key'];
                unset($params['key']);
            }

            $params['merchant_id'] = yii::$app->session['uid'];
            $transaction = yii::$app->db->beginTransaction();
            $templateModel = new ShopExpressTemplateDetailsModel();
            try {
                $templateData['id'] = $params['id'];
                $templateData['merchant_id'] = yii::$app->session['uid'];
                $templateData['name'] = $params['name'];
                $templateData['type'] = $params['type'];
                $array = $model->update($templateData); //模板
                $data['merchant_id'] = yii::$app->session['uid'];
                //模板详情信息
                $templateModel->deleteTrue(['shop_express_template_id' => $params['id']]);
                for ($i = 0; $i < count($params['names']); $i++) {
                    $data['shop_express_template_id'] = $params['id'];
                    $data['names'] = $params['names'][$i];
                    $data['first_num'] = $params['first_num'][$i];
                    $data['first_price'] = $params['first_price'][$i];
                    $data['expand_num'] = $params['expand_num'][$i];
                    $data['expand_price'] = $params['expand_price'][$i];
                    $data['status'] = 1;
                    $array = $templateModel->add($data);
                }
                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                return result(200, "更新成功");
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "更新失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdates($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopExpressTemplateModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $data['`key`'] = $params['key'];
            unset($params['key']);
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['status'] = 0;
            $array = $model->update($data);
            $data['id'] = $id;
            $data['status'] = 1;
            $array = $model->update($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new ShopExpressTemplateModel();
            $templateModel = new ShopExpressTemplateDetailsModel();
            $params['id'] = $id;
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $transaction = yii::$app->db->beginTransaction();
                $templateModel = new ShopExpressTemplateDetailsModel();
                try {
                    $array = $model->delete($params);
                    $templateModel->deleteTrue(['shop_express_template_id' => $params['id'], '`key`' => $params['`key`'], 'merchant_id' => $params['merchant_id']]);
                    $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                    return result(200, "删除成功");
                } catch (Exception $e) {
                    $transaction->rollBack(); //回滚
                    return result(500, "删除失败");
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
