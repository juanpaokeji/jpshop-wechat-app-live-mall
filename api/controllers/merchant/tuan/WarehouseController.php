<?php

namespace app\controllers\merchant\tuan;

use app\models\merchant\storehouse\StorehouseModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\tuan\LeaderModel;
use app\models\tuan\UserModel;
use Yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\tuan\WarehouseModel;
use app\models\shop\TuanLeaderModel;

class WarehouseController extends MerchantController
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

            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }

            $model = new WarehouseModel();
            $array = $model->do_select($params);

            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['leaders'] = ($array['data'][$i]['leaders'] == "" || $array['data'][$i]['leaders'] == "Array") ? array() : json_decode($array['data'][$i]['leaders'], true);
                    $array['data'][$i]['leaders_number'] = isset($array['data'][$i]['leaders'])?count($array['data'][$i]['leaders']):0;
                    if (count($array['data'][$i]['leaders']) != 0) {
                        $leaderModel = new LeaderModel();
                        $leader = $leaderModel->do_select(['in' => ['id', $array['data'][$i]['leaders']], 'limit' => false]);
                        if ($leader['status'] == 200) {
                            $array['data'][$i]['leader_info'] = $leader['data'];
                        } else {
                            $array['data'][$i]['leader_info'] = array();
                        }
                    } else {
                        $array['data'][$i]['leader_info'] = array();
                    }
                    $array['data'][$i]['houses'] = $array['data'][$i]['houses'] == "" ? array() : json_decode($array['data'][$i]['houses'], true);
                    $array['data'][$i]['houses_number'] = count($array['data'][$i]['houses']);
                    if (count($array['data'][$i]['houses']) != 0) {
                        $storehouses = new StorehouseModel();
                        $house = $storehouses->do_select(['in' => ['id', $array['data'][$i]['houses']], 'limit' => false]);
                        if ($house['status'] == 200) {
                            $array['data'][$i]['house_info'] = $house['data'];
                        } else {
                            $array['data'][$i]['house_info'] = array();
                        }
                    } else {
                        $array['data'][$i]['house_info'] = array();
                    }
                }

            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionHouse($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new WarehouseModel();
            $array = $model->do_one(['id' => $id]);
            $storehouses = new StorehouseModel();
            if ($array['data']['houses'] == "") {
                $house = array();
            } else {
                $house = json_decode($array['data']['houses'], true);
            }
            $data['field'] = "id,name,address";
            $data['not in'] = ['id', $house];
            $data['limit'] = isset($params['limit']) ? $params['limit'] : 10;
            $house = $storehouses->do_select($data);
            return $house;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionLeader($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new WarehouseModel();
            $array = $model->do_one(['id' => $id]);
            $leaderModel = new LeaderModel();
            if ($array['data']['leaders'] == "") {
                $leader = array();
            } else {
                $leader = json_decode($array['data']['leaders'], true);
            }
            $data['field'] = "id,realname,area_name";
            $data['not in'] = ['id', $leader];
            $data['limit'] = isset($params['limit']) ? $params['limit'] : 10;
            $leader = $leaderModel->do_select($data);
            return $leader;
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

            $must = ['key', 'name','status'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $model = new WarehouseModel();
            $array = $model->do_add($params);
            if ($array['status'] == 200) {
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
            if ($array['status'] == 200) {
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

            $model = new WarehouseModel();
            $where['id'] = $id;
            if (isset($params['leaders'])) {
                if ($params['leaders'] == "false") {
                    $params['leaders'] = "";
                }

            }
            if (isset($params['houses'])) {
                if ($params['houses'] == "false") {
                    $params['houses'] = "";
                }

            }
            $array = $model->do_update($where, $params);

            if ($array['status'] == 200) {
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


}