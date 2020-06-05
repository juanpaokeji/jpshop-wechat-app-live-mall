<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\ShopExpressTemplateModel;
use app\models\shop\ShopExpressTemplateDetailsModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TemplateController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new ShopExpressTemplateModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
//            $params['shop_express.`key`'] = $params['key'];
//            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['supplier_id'] = 0;
            // $params['fields'] ="";
            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //快递公司列表
    public function actionAll()
    {
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

    public function actionSingle($id)
    {
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

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new ShopExpressTemplateModel();
            //设置类目 参数
            if ($params['type'] != 3) {
                $must = ['names'];
                $rs = $this->checkInput($must, $params);
                if ($rs != false) {
                    return $rs;
                }
            } else {
                $must = ['distance'];
                $rs = $this->checkInput($must, $params);
                if ($rs != false) {
                    return $rs;
                }
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
                //模板详情信息'
                if ($params['type'] == 3) {
                    $data['status'] = 1;
                    $data['shop_express_template_id'] = $list['data'];
                    $data['distance'] = json_encode($params['distance']);
                    $array = $templateModel->add($data);
                } else {
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
                }
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $list['data'];
                $operationRecordData['module_name'] = '运费模板';
                $operationRecordModel->do_add($operationRecordData);

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

    public function actionUpdate($id)
    {
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
                if ($params['type'] == 3) {
                    $data['status'] = 1;
                    $data['shop_express_template_id'] =$params['id'];
                    $data['distance'] = json_encode($params['distance']);
                    $array = $templateModel->add($data);
                } else {
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
                }
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['`key`'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '运费模板';
                    $operationRecordModel->do_add($operationRecordData);
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

    public function actionUpdates($id)
    {
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
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $data['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '运费模板';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
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
                    if ($array['status'] == 200){
                        //添加操作记录
                        $operationRecordModel = new OperationRecordModel();
                        $operationRecordData['key'] = $params['`key`'];
                        $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                        $operationRecordData['operation_type'] = '删除';
                        $operationRecordData['operation_id'] = $id;
                        $operationRecordData['module_name'] = '运费模板';
                        $operationRecordModel->do_add($operationRecordData);
                    }
                    $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                    return result(200, "删除成功");
                } catch (Exception $e) {
                    $transaction->rollBack(); //回滚
                    return result(500, "删除失败");
                }
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
