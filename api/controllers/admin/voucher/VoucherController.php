<?php

namespace app\controllers\admin\voucher;

use yii;
use yii\web\CommonController;
use yii\db\Exception;
use app\models\admin\voucher\VoucherModel;
use app\models\admin\voucher\VoucherTypeModel;

/**
 * 抵用卷控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class VoucherController extends CommonController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function generateCode($nums = 1, $exist_array = '', $code_length = 32, $prefix = '') {

        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz";
        $promotion_codes = array(); //这个数组用来接收生成的优惠码
        for ($j = 0; $j < $nums; $j++) {
            $code = '';
            for ($i = 0; $i < $code_length; $i++) {

                $code .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
            if (!in_array($code, $promotion_codes)) {
                if (is_array($exist_array)) {
                    if (!in_array($code, $exist_array)) {//排除已经使用的优惠码
                        $promotion_codes[$j] = $prefix . $code; //将生成的新优惠码赋值给promotion_codes数组
                    } else {
                        $j--;
                    }
                } else {
                    $promotion_codes[$j] = $prefix . $code; //将优惠码赋值给数组
                }
            } else {
                $j--;
            }
        }
        return $promotion_codes[0];
    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $voucher = new VoucherModel();
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
            $voucher = new VoucherModel();
            $params['cdkey'] = $id;
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
            $voucher = new VoucherModel();
            $must = ['type_id'];
            $params['cdkey'] = $this->generateCode();
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            //获取优惠券类型
            $type = new VoucherTypeModel();
            $typedata['id'] = $params['type_id'];
            $voutype = $type->find($typedata);
            if ($voutype['status'] == 204) {
                $array = ['status' => 400, 'message' => '该type_id不存在',];
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            //优惠券新增参数
            $vdata['cdkey'] = $params['cdkey'];
            $vdata['type_id'] = $params['type_id'];
            $vdata['type_name'] = $voutype['data']['name'];

            $vdata['status'] = $params['status'];
            if ($params['merchant_id']) {
                $vdata['start_time'] = time();
                $vdata['end_time'] = ($voutype['data']['days'] * 24 * 60 * 60) + ($vdata['start_time']);
                $vdata['is_exchange'] = 1;
                $vdata['merchant_id'] = $params['merchant_id'];
            } else {
                $vdata['is_exchange'] = 0;
            }
            $vdata['is_used'] = 0;
            $vdata['price'] = $voutype['data']['price'];
            $vdata['full_price'] = $voutype['data']['full_price'];
            //开始事务
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $array = $voucher->add($vdata);
                //更新优惠券个数
                $typeparams['send_count'] = $voutype['data']['send_count'] + 1;
                $typeparams['id'] = $params['type_id'];

                $type->update($typeparams);
                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "添加失败");
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
            $params['cdkey'] = $id;
            $voucher = new VoucherModel();
            if (!isset($params['cdkey'])) {
                return result(400, "缺少参数 cdkey");
            } else {
                if (isset($params['is_used'])) {
                    //params 参数值设置
                    $params['update_time'] = time();
                }
                if (isset($params['type_id'])) {
                    //获取优惠券类型
                    //  if($params['type_id']){}
                    $type = new VoucherTypeModel();
                    $typedata['id'] = $params['type_id'];
                    $voutype = $type->find($typedata);
                    $params['price'] = $voutype['data']['price'];
                    $params['full_price'] = $voutype['data']['full_price'];
                    $params['type_name'] = $voutype['data']['name'];
                }
                $array = $voucher->update($params);
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
            $voucher = new VoucherModel();
            $params['cdkey'] = $id;
            if (!isset($params['cdkey'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $voucher->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
