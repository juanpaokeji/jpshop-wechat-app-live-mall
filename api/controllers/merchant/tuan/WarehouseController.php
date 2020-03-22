<?php

namespace app\controllers\merchant\tuan;

use app\models\merchant\system\OperationRecordModel;
use app\models\tuan\LeaderModel;
use app\models\tuan\UserModel;
use Yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\tuan\WarehouseModel;
use app\models\shop\TuanLeaderModel;

class WarehouseController extends MerchantController {

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

            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }

            $model = new WarehouseModel();
            $sql = "SELECT count(shop_tuan_leader.warehouse_id) as leader_num,shop_tuan_warehouse.id FROM shop_tuan_leader LEFT JOIN shop_tuan_warehouse ON shop_tuan_warehouse.id = shop_tuan_leader.warehouse_id GROUP BY shop_tuan_warehouse.id";
            $data = Yii::$app->db->createCommand($sql)->queryAll();
            $array = $model->do_select($params);
            if ($array['status'] == 200) {
                foreach ($array['data'] as $key=>$val){
                    foreach ($data as $k=>$v){
                        if ($val['id'] == $v['id']){
                            $array['data'][$key]['leader_num'] = $v['leader_num'];
                        }
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new WarehouseModel();
            $params['id'] = $id;
            $array = $model->do_one($params);

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
            $params['merchant_id'] = yii::$app->session['uid'];

            $must = ['key', 'name', 'realname', 'phone', 'addr', 'coordinate', 'status'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if (stristr($params['coordinate'],',')){
                $coordinate = explode(',',$params['coordinate']);
            } elseif (stristr($params['coordinate'],'，')) {
                $coordinate = explode('，',$params['coordinate']);
            } else {
                return result(500, "坐标有误");
            }
            $params['longitude'] = $coordinate[0];
            $params['latitude'] = $coordinate[1];
            unset($params['coordinate']);
            $model = new WarehouseModel();
            $array = $model->do_add($params);

            $userModel = new LeaderModel();
         //   $userModel->do_update(['warehouse_id'=>$id],['warehouse_id'=>0]);
            $userModel->do_update(['uid'=>$params['leader_uid']],['warehouse_id'=>$array['data']]);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '路线';
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

            $model = new WarehouseModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '路线';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            if (isset($params['coordinate'])){
                if (stristr($params['coordinate'],',')){
                    $coordinate = explode(',',$params['coordinate']);
                } elseif (stristr($params['coordinate'],'，')) {
                    $coordinate = explode('，',$params['coordinate']);
                } else {
                    return result(500, "坐标有误");
                }
                $params['longitude'] = $coordinate[0];

                $params['latitude'] = $coordinate[1];

                unset($params['coordinate']);
            }

            $model = new WarehouseModel();
            $where['id'] = $id;
            $array = $model->do_update($where, $params);

            if (isset($params['leader_uid'])){
                $userModel = new LeaderModel();
                $userModel->do_update(['warehouse_id'=>$id],['warehouse_id'=>0]);
                $userModel->do_update(['uid'=>$params['leader_uid']],['warehouse_id'=>$id]);
            }
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '路线';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionLeader($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new TuanLeaderModel();
            $params['warehouse_id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            unset($params['id']);
            $array = $model->get_list($params);
            if (!$array){
                return result(204, "查询失败");
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }


}