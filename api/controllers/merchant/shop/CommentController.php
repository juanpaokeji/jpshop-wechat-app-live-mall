<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\CommentModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class CommentController extends MerchantController {

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
            $model = new CommentModel();
            $params['shop_user_comment.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_user_comment.merchant_id'] = yii::$app->session['uid'];
            $params['orderby'] = " shop_user_comment.id desc ";
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
            $category = new CommentModel();
            $params['id'] = $id;
            $array = $category->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new CommentModel();
            $params['id'] = $id;
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->update($params);
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['`key`'];
                    if (isset(yii::$app->session['sid'])) {
                        $subModel = new \app\models\merchant\system\UserModel();
                        $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                        if ($subInfo['status'] == 200){
                            $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                        }
                    } else {
                        $merchantModle = new MerchantModel();
                        $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                        if ($merchantInfo['status'] == 200) {
                            $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                        }
                    }
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '评论管理';
                    $operationRecordModel->do_add($operationRecordData);
                }
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
            $model = new CommentModel();
            $params['id'] = $id;
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                if (isset(yii::$app->session['sid'])) {
                    $subModel = new \app\models\merchant\system\UserModel();
                    $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                    if ($subInfo['status'] == 200){
                        $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                    }
                } else {
                    $merchantModle = new MerchantModel();
                    $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                    if ($merchantInfo['status'] == 200) {
                        $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                    }
                }
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '评论管理';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
