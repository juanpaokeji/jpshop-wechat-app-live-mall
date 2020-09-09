<?php

namespace app\controllers\supplier\goods;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\system\UserModel;
use app\models\merchant\user\MerchantModel;
use app\models\tuan\LeaderModel;
use yii;
use yii\db\Exception;
use yii\web\SupplierController;
use app\models\shop\AfterInfoModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class AfterinfoController extends SupplierController
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
            $model = new AfterInfoModel();
            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
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
            $category = new AfterInfoModel();

            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);

            $params['merchant_id'] = yii::$app->session['uid'];
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
            $model = new AfterInfoModel();


            $params['merchant_id'] = yii::$app->session['uid'];
            $params['after_addr'] = $params['province'] . $params['city'] . $params['area'] . $params['address'];
            $array = $model->add($params);
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

            $params['`key`'] = yii::$app->session['key'];
            unset($params['key']);
            unset($params['delete_time']);
            unset($params['update_time']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            $model = new AfterInfoModel();
            $params['after_addr'] = $params['province'] . $params['city'] . $params['area'] . $params['address'];
            $array = $model->update($params);
            if ($array['status'] == 200) {
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
                $operationRecordData['module_name'] = '收货信息';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionDelete($id) {
//        if (yii::$app->request->isDelete) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new AfterInfoModel();
//            $params['id'] = $id;
//
//            $params['`key`'] = yii::$app->session['key'];
//            unset($params['key']);
//
//            $params['merchant_id'] = yii::$app->session['uid'];
//            if (!isset($params['id'])) {
//                return result(400, "缺少参数 id");
//            } else {
//                $array = $model->delete($params);
//            }
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    public function actionUpdateInfo()
    {
        $request = request(); //获取 request 对象 及方法
        $method = $request['method'];
        if ($method != 'PUT') {
            return result(500, "请求方式错误");
        }
        $params = $request['params'];
        $params['id'] = yii::$app->session['sid'];
        $table = new UserModel();
        $id = yii::$app->session['sid'];

        $user = $table->find(['id' => $id]);
        if (isset($params['leader'])) {
            $leader = $params['leader'];
            $params['leader'] = json_encode($params['leader'], JSON_UNESCAPED_UNICODE);
            if ($user['status'] == 200) {
                if ($user['data']['type'] == 1) {
                    $leaderModel = new LeaderModel();
                    $data = array(
                        'key' => yii::$app->session['key'],
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
                        $params['id'] = $id;
                        $array = $table->updatemd($params);
                        return $array;
                    } else {
                        return $res;
                    }
                }
            }
            return result(200, "更新成功");
        }
    }

    public function actionInfo()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $userModel = new \app\models\merchant\system\UserModel();
            $data['`key`'] = yii::$app->session['key'];
            $data['id'] = yii::$app->session['sid'];
            $array = $userModel->find($data);
            if ($array['status'] == 200) {
                $array['data']['leader'] = json_decode($array['data']['leader'], true);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdatePassword()
    {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->bodyParams; //获取body传参
        $merchant = new UserModel();
        $params['id'] = yii::$app->session['sid'];

        $res = $merchant->find(['id'=>yii::$app->session['sid']]);
        if ($res['status'] != 200) {
            return $res;
        }
        if ($res['data']['password'] != md5($params['old'] . $res['data']['salt'])) {
            return result(500, '原密码不正确');
        }
        $salt = $this->get_randomstr(32);
        $params['password'] = md5($params['password'] . $salt);
        $params['salt'] = $salt;
        unset($params['old']);
        unset($params['confirm_new']);
        $array = $merchant->updatemd($params);
        return $array;

    }

}
