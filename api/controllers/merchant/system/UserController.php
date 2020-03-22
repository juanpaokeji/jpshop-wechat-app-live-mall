<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use app\models\tuan\LeaderModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\system\UserModel;
use app\models\wolive\ServiceModel;
use app\models\core\TableModel;
use app\models\shop\SaleGoodsModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class UserController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new UserModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->findall($params);
            if ($array['status'] == 200) {
                foreach ($array['data'] as $k => $v) {
                    if (!empty($v['leader'])) {
                        $array['data'][$k]['leader'] = json_decode($v['leader'], true);
                    }
                }
                $goodsModel = new SaleGoodsModel();
                $table = new TableModel();

                $number = $array['count'];
                for ($i = 0; $i < $number; $i++) {
                    if ($array['data'][$i]['type'] == 1) {
                        $res = $goodsModel->do_select(['supplier_id' => $array['data'][$i]['id'], 'status' => 1]);
                        if ($res['status'] == 200) {
                            $array['data'][$i]['goods_number'] = count($res['data']);
                        } else {
                            $array['data'][$i]['goods_number'] = 0;
                        }
                        $sql = "select sum(payment_money)as order_money from shop_order_group where supplier_id = {$array['data'][$i]['id']}";
                        $order = $table->querySql($sql);

                        if ($order[0]['order_money'] != null) {
                            $array['data'][$i]['order_money'] = $order[0]['order_money'];
                        } else {
                            $array['data'][$i]['order_money'] = 0;
                        }
                    }
                }

            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionKefu()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new UserModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->kefu($params);
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
            $model = new UserModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $array = $model->find($params);
            if ($array['status'] == 200 && !empty($array['data']['leader'])) {
                $array['data']['leader'] = json_decode($array['data']['leader'], true);
            }
            if ($array['status'] == 200 && !empty($array['data']['yly_config'])) {
                $array['data']['yly_config'] = json_decode($array['data']['yly_config'], true);
            }


            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'POST') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $params = $request['params'];

        $table = new UserModel();
        /**
         * 新增
         * 创建用户的时候，随机生成一个32位随机数然后md5获取salt值，将用户输入的md5(password+salt)存入password
         */
        if (isset($params['is_kefu'])) {
            if ($params['is_kefu'] == 1) {
                $rule = $table->tableSingle('shop_auth_group', ['is_kefu' => 1, '`key`' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
                $params['group_id'] = $rule['id'];
            }
        }
        $must = ['username', 'password', 'group_id'];
        if ($params['type'] == 1) {
            $must = ['username', 'password'];
        }

        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return $checkRes;
        }
        $res = $table->find(['username' => $params['username']]);
        //返回错误
        if ($res['status'] != 200) {
            return $res;
        }
        //正确返回，判断是否存在数据

        if (count($res['data']) != 0) {
            return result(409, '该用户名已存在', $res);
        }
        //获取 md5 加密的 32 位随机字符串
        $params['salt'] = md5($this->get_randomstr(32));
        $params['`key`'] = $params['key'];
        unset($params['key']);
        $params['merchant_id'] = yii::$app->session['uid'];
        $operationRecordData['module_name'] = isset($params['is_kefu']) ? '客服管理' : '员工管理';
        if (isset($params['is_kefu'])) {
            if ($params['is_kefu'] == 1) {
                $serviceModel = new ServiceModel();
                $sdata = array(
                    'user_name' => $params['username'],
                    'nick_name' => "在线客服",
                    'real_name' => $params['real_name'],
                    'password' => md5($params['password'] . $params['salt']),
                    'salt' => $params['salt'],
                    'groupid' => '0',
                    'email' => '',
                    'business_id' => $params['`key`'],
                    'avatar' => "/assets/images/index/juanpao_logo.png",
                    'level' => 'manager',
                    'parent_id' => '0',
                    'state' => 'offline',
                );
                $serviceModel->add($sdata);
            }
            unset($params['is_kefu']);
        }
        if (isset($params['leader'])) {
            $leader = $params['leader'];
            $params['leader'] = json_encode($params['leader'], JSON_UNESCAPED_UNICODE);

            $transaction = yii::$app->db->beginTransaction();
            $array = $table->add($params);
            if ($array['status'] != 200) {
                $transaction->rollBack(); //回滚
                return result(500, "添加失败");
            }
            try {
                if ($params['type'] == 1) {
                    $leaderModel = new LeaderModel();
                    $data = array(
                        'key' => $params['`key`'],
                        'merchant_id' => yii::$app->session['uid'],
                        'supplier_id' => $array['data'],
                        'area_name' => $leader['area_name'],
                        'province_code' => $leader['province_code'],
                        'city_code' => $leader['city_code'],
                        'area_code' => $leader['area_code'],
                        'is_self' => 1,
                        'addr' => $leader['addr'],
                        'longitude' => $leader['longitude'],
                        'latitude' => $leader['latitude'],
                        'realname' => $leader['realname'],
                        'tuan_express_fee' => $leader['tuan_express_fee'],
                        'is_tuan_express' => $leader['is_tuan_express'],
                        'phone' => $leader['phone'],
                        'status' => 1,
                    );
                    $res = $leaderModel->do_add($data);
                    if ($res['status'] == 200) {
                        $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                    } else {
                        $transaction->rollBack(); //回滚
                        return result(500, "添加失败");
                    }
                }
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['`key`'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '新增';
                    $operationRecordData['operation_id'] = $array['data'];
                    $operationRecordData['module_name'] = '门店';
                    $operationRecordModel->do_add($operationRecordData);
                }
                return result(200, "添加成功");
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "添加失败");
            }
        } else {
            $array = $table->add($params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['`key`'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        }
    }

    public function actionUpdate($id)
    {
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(500, "请求方式错误");
        }
        $params = $request['params'];
        $params['id'] = $id;
        $table = new UserModel();
        if (!isset($params['username'])) {//存在 status 则表示修改状态字段
            $array = $table->update($params);
        } else {
            if (isset($params['is_kefu'])) {
                if ($params['is_kefu'] == 1) {
                    $rule = $table->tableSingle('shop_auth_group', ['is_kefu' => 1, '`key`' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
                    $params['group_id'] = $rule['id'];
                }
            }
            $must = ['username', 'group_id'];
            $user = $table->find(['id' => $id]);
            if ($user['status'] == 200) {
                if ($user['data']['type'] == 1) {
                    $must = ['username'];
                }
            }

            $checkRes = $this->checkInput($must, $params);
            if ($checkRes != false) {
                return $checkRes;
            }
            //判断用户名是否重复
            $res = $table->find(['username' => $params['username'], 'id != ' . $id => null]);
            //返回错误
            if ($res['status'] != 200) {
                return json_encode($res);
            }
            //正确返回，判断是否存在数据
            if (count($res['data']) != 0) {
                $array = [
                    'status' => 409,
                    'message' => '该用户名已存在',
                ];
                return $array;
            }
            //获取该用户的盐
            $res = $table->find(['id' => $id]);
            if ($res['status'] != 200) {
                return json_encode($res, JSON_UNESCAPED_UNICODE);
            }

            $params['salt'] = $res['data']['salt'];
            if (isset($params['leader'])) {
                $leader = $params['leader'];
                $params['leader'] = json_encode($params['leader'], JSON_UNESCAPED_UNICODE);
                $transaction = yii::$app->db->beginTransaction();
                try {
                    if ($user['status'] == 200) {
                        if ($user['data']['type'] == 1) {
                            $leaderModel = new LeaderModel();
                            $data = array(
                                'key' => $params['key'],
                                'merchant_id' => yii::$app->session['uid'],
                                'supplier_id' => $params['id'],
                                'area_name' => $leader['area_name'],
                                'province_code' => $leader['province_code'],
                                'city_code' => $leader['city_code'],
                                'area_code' => $leader['area_code'],
                                'is_self' => 1,
                                'addr' => $leader['addr'],
                                'longitude' => $leader['longitude'],
                                'latitude' => $leader['latitude'],
                                'realname' => $leader['realname'],
                                'tuan_express_fee' => $leader['tuan_express_fee'],
                                'is_tuan_express' => $leader['is_tuan_express'],
                                'phone' => $leader['phone'],
                                'status' => 1,
                            );
                            $res = $leaderModel->do_update(['supplier_id' => $id], $data);
                            if ($res['status'] == 200) {
                                unset($params['leader']);
                                $array = $table->update($params);
                                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
                            } else {
                                $transaction->rollBack(); //回滚
                                return result(500, "更新失败");
                            }
                        }
                    }
                    if ($array['status'] == 200){
                        //添加操作记录
                        $operationRecordModel = new OperationRecordModel();
                        $operationRecordData['key'] = $params['key'];
                        $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                        $operationRecordData['operation_type'] = '更新';
                        $operationRecordData['operation_id'] = $id;
                        $operationRecordData['module_name'] = '门店';
                        $operationRecordModel->do_add($operationRecordData);
                    }
                    return result(200, "更新成功");
                } catch (Exception $e) {
                    $transaction->rollBack(); //回滚
                    return result(500, "更新失败");
                }

            }else{
                $array = $table->update($params);
            }

        }
        if ($array['status'] == 200){
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['key'];
            $operationRecordData['merchant_id'] = yii::$app->session['uid'];
            $operationRecordData['operation_type'] = '更新';
            $operationRecordData['operation_id'] = $id;
            $operationRecordData['module_name'] = isset($params['is_kefu']) ? '客服管理' : '员工管理';
            $operationRecordModel->do_add($operationRecordData);
        }
        return $array;
    }

    public function actionDelete($id)
    {
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        $params = $request['params'];
        if ($method != 'DELETE') {
            return result(404, $this->requestMethodErrorMsg);
        }
        $table = new UserModel();
        $array = $table->delete(['id' => $id]);
        if ($array['status'] == 200){
            //添加操作记录
            $operationRecordModel = new OperationRecordModel();
            $operationRecordData['key'] = $params['key'];
            $operationRecordData['merchant_id'] = yii::$app->session['uid'];
            $operationRecordData['operation_type'] = '删除';
            $operationRecordData['operation_id'] = $id;
            $operationRecordData['module_name'] = isset($params['is_kefu']) ? '客服管理' : '员工管理';
            $operationRecordModel->do_add($operationRecordData);
        }
        return $array;
    }

    //小票机配置更新
    public function actionYlyupdate($id)
    {
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(500, "请求方式错误");
        }
        $params = $request['params'];
        $params['id'] = $id;
        $table = new UserModel();
        if (isset($params['yly_config'])) {
            $params['yly_config'] = json_encode($params['yly_config'], JSON_UNESCAPED_UNICODE);
        }
        $array = $table->ylyupdate($params);

        return $array;
    }

}
