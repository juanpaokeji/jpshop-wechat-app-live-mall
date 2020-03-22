<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\GoodsModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class RecruitsController extends MerchantController {

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
            $model = new GoodsModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['delete_time'] = 1;
            $params['is_recruits'] = 1;

            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new GoodsModel();

            $goodsData = array(
                'id' => $id,
                'is_recruits' => 0,
            );

            $array = $model->update($goodsData);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '新人专享';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionGoodslist() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new GoodsModel();
            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['delete_time'] = 1;
            $params['is_flash_sale'] = 0;  //新人专享活动不能与秒杀、砍价、拼团活动同时开启
            $params['is_open_assemble'] = 0;
            $params['is_bargain'] = 0;
            $params['is_recruits'] = 0;
            $params['status'] = 1;

            $array = $model->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $must = ['key','goods_ids'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $sql = "UPDATE `shop_goods` SET `is_recruits` = CASE id";
            foreach ($params['goods_ids'] as $k=>$v){
                $sql .= " WHEN $v THEN '1'";
            }
            $sql .= " END WHERE id IN (";
            foreach ($params['goods_ids'] as $k=>$v){
                $sql .= "$v,";
            }
            $sql = substr($sql, 0, -1);
            $sql .= ")";
            try {
                //开始事务
                $transaction = Yii::$app->db->beginTransaction();
                $array = Yii::$app->db->createCommand($sql)->execute();
                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            } catch (\yii\base\Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "添加失败");
            }
            if ($array) {
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = json_encode($params['goods_ids']);
                $operationRecordData['module_name'] = '新人专享';
                $operationRecordModel->do_add($operationRecordData);
                return result(200, '请求成功');
            } else {
                return result(500, '新增失败');
            }
        } else {
            return result(500, "请求方式错误");
        }
    }


}
