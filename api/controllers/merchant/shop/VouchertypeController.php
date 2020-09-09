<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use yii;
use yii\web\MerchantController;
use yii\base\Exception;
use app\models\shop\VoucherTypeModel;
use app\controllers\common\CommonController;
use app\models\shop\VoucherModel;
use app\models\merchant\app\AppAccessModel;

/**
 * 抵用卷类型表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class VouchertypeController extends MerchantController {

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
            $voucher = new VoucherTypeModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];

            $array = $voucher->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }
    
    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $voucher = new VoucherTypeModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
			$params["type in (1,2,4,5)"] = null;
            $array = $voucher->findall($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $voucher = new VoucherTypeModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            $array = $voucher->find($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $voucher = new VoucherTypeModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $must = ['name', 'price', 'full_price', 'count', 'from_date', 'to_date','type'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if($params['type'] != 3){
                $params['min_price'] = 0.00;
                $params['lucky_price'] = 0.00;
                $params['lucky_min_price'] = 0.00;
            }else{
                if(!is_numeric($params['min_price']) || empty($params['min_price']) || !is_numeric($params['lucky_price']) || empty($params['lucky_price']) || !is_numeric($params['lucky_min_price']) || empty($params['lucky_min_price'])){
                    return result(500, "红包金额设置错误不能为空！");
                }
                //如果type = 3 检测当前应用是否创建过 一个应用只能创建一个运气红包配置
                $where['`key`'] = $params['`key`'];
                $where['merchant_id'] = $params['merchant_id'];
                $where['type'] = 3;
                $where['status'] = 1;
                $info = $voucher->finds($where);
                if($info['status'] == 200){
                    return result(500, "当前应用已创建了运气红包");
                }
                if($params['min_price'] > $params['price']){
                    return result(500, "运气红包小面额最大面值要大于运气红包小面额最小值");
                }
                if($params['lucky_min_price'] > $params['lucky_price']){
                    return result(500, "运气红包大面额最大面值要大于运气红包大面额最小值");
                }
            }
            $data['merchant_id'] = $params['merchant_id'];
            $data['`key`'] = $params['`key`'];
            $app = new AppAccessModel();

            $res = $app->find($data);

            if ($res['status'] == 200) {
                if ($res['data']['config'] != "") {
                    $res['data']['config'] = json_decode($res['data']['config'], true);
                    if ($res['data']['config']['is_large_scale'] == 0) {
                        if ($params['price'] > $params['full_price'] / 2) {
                            return result(500, "请去优惠卷设置打开大比例优惠卷");
                        }
                    }
                    if ($res['data']['config']['number'] < $params['count']) {
                        return result(500, "已超过最大个数，请去优惠卷设置修改！");
                    }
                }
            }

            $params['from_date'] = strtotime($params['from_date']);
            $params['to_date'] = strtotime($params['to_date']);
            if ($rs != false) {
                return $rs;
            }
            $params['days'] = ceil(bcdiv(bcsub($params['to_date'], $params['from_date']), 86400,2)) +1;
            $params['set_online_time'] = $params['from_date'];
            $params['status'] = 1;
            $array = $voucher->add($params);
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
                $operationRecordData['operation_type'] = '新增';
                $operationRecordData['operation_id'] = $array['data'];
                $operationRecordData['module_name'] = '优惠券';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $params['id'] = $id;
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $data['merchant_id'] = $params['merchant_id'];
            $data['`key`'] = $params['`key`'];
            $app = new AppAccessModel();
            $res = $app->find($data);
            if ($res['status'] == 200) {
                if ($res['data']['config'] != "") {
                    $res['data']['config'] = json_decode($res['data']['config'], true);
                    if ($res['data']['config']['is_large_scale'] == 0) {
                        if ($params['price'] > $params['full_price'] / 2) {
                            return result(500, "请去优惠卷设置打开大比例优惠卷");
                        }
                    }
                    if (isset($params['count']) && $res['data']['config']['number'] < $params['count']) {
                        return result(500, "已超过最大个数，请去优惠卷设置修改！");
                    }
                }
            }

            $params['merchant_id'] = yii::$app->session['uid'];
            $voucher = new VoucherTypeModel();
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if (isset($params['from_date'])) {
                    $params['from_date'] = strtotime($params['from_date']);
                }
                if (isset($params['to_date'])) {
                    $params['to_date'] = strtotime($params['to_date']);
                }
                if (isset($params['from_date'])) {
                    $params['set_online_time'] = $params['from_date'];
                }
                if (isset($params['type'])){
                    if($params['type'] != 3){
                        $params['min_price'] = 0.00;
                        $params['lucky_price'] = 0.00;
                        $params['lucky_min_price'] = 0.00;
                    }else{
                        if(!is_numeric($params['min_price']) || empty($params['min_price']) || !is_numeric($params['lucky_price']) || empty($params['lucky_price']) || !is_numeric($params['lucky_min_price']) || empty($params['lucky_min_price'])){
                            return result(500, "红包金额设置错误不能为空！");
                        }
                        if($params['min_price'] > $params['price']){
                            return result(500, "运气红包小面额最大面值要大于运气红包小面额最小值");
                        }
                        if($params['lucky_min_price'] > $params['lucky_price']){
                            return result(500, "运气红包大面额最大面值要大于运气红包大面额最小值");
                        }
                    }
                }
                if (isset($params['to_date'])){
                    $params['days'] = ceil(bcdiv(bcsub($params['to_date'], $params['from_date']), 86400,2)) + 1;
                }
                $array = $voucher->update($params);
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
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '优惠券';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $voucher = new VoucherTypeModel();
            if (isset($params['key'])) {
                $params['`key`'] = $params['key'];
                unset($params['key']);
            }
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['id'] = $id;
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $voucher->delete($params);
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
                $operationRecordData['module_name'] = '优惠券';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function voucher($number, $params) {
        $voucher = new VoucherModel();
        $cc = new CommonController();
        $transaction = yii::$app->db->beginTransaction();
        try {
            for ($i = 0; $i < $number; $i++) {
                do {
                    $vdata['cdkey'] = $cc->generateCode();
                    $rs = $voucher->find($vdata);
                } while ($rs['status'] == 200);

                $vdata['type_id'] = $params['type_id'];
                $vdata['type_name'] = $params['name'];
                $vdata['status'] = $params['status'];
                $vdata['start_time'] = time();
                $vdata['end_time'] = ($params['data']['days'] * 24 * 60 * 60) + ($vdata['start_time']);
                $vdata['is_exchange'] = 0;
                $vdata['merchant_id'] = $params['merchant_id'];
                $vdata['`key`'] = $params['`key`'];
                $vdata['is_used'] = 0;
                $vdata['price'] = $params['price'];
                $vdata['full_price'] = $params['full_price'];
                $array = $voucher->add($vdata);
            }
            $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            return result(200, "新增成功");
        } catch (Exception $e) {
            $transaction->rollBack(); //回滚
            return result(500, "新增失败");
        }
    }

}
