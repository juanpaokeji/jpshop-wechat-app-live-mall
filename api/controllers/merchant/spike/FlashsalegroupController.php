<?php

namespace app\controllers\merchant\spike;

use app\models\merchant\system\OperationRecordModel;
use app\models\spike\FlashSaleModel;
use yii;
use yii\web\MerchantController;
use app\models\spike\FlashSaleGroupModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

class FlashsalegroupController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

//    public function behaviors() {
//        return [
//            'token' => [
//                'class' => 'yii\filters\MerchantFilter', //调用过滤器
////                'only' => ['single'],//指定控制器应用到哪些动作
//                'except' => ['sms', 'register', 'password', 'all'], //指定控制器不应用到哪些动作
//            ]
//        ];
//    }

    public function actionList() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new FlashSaleGroupModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            if (isset($params['type'])) {
                if ($params['type'] == "") {
                    unset($params['type']);
                }
            }
            $array = $model->do_select($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new FlashSaleGroupModel();
            $params['id'] = $id;
            $array = $model->do_one($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new FlashSaleGroupModel();
            $must = ['name', 'pic_url', 'money', 'type', 'detail_info', 'status'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            if ($params['pic_url'] != "") {
                $base = new Base64Model();
                $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/decoration");
                $cos = new CosModel();
                $cosRes = $cos->putObject($params['pic_url']);
                if ($cosRes['status'] == '200') {
                    $url = $cosRes['data'];
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                } else {
                    unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                    return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                }
                $params['pic_url'] = $url;
            }
            //  $params['count'] = (int) $params['count'];

            $array = $model->do_add($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new FlashSaleGroupModel();
            $where['id'] = $id;
            if (isset($params['pic_url'])) {
                if ($params['pic_url'] != "") {
                    $base = new Base64Model();
                    $params['pic_url'] = $base->base64_image_content($params['pic_url'], "./uploads/admin/vip");
                    $cos = new CosModel();
                    $cosRes = $cos->putObject($params['pic_url']);
                    if ($cosRes['status'] == '200') {
                        $url = $cosRes['data'];
                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                    } else {
                        unlink(Yii::getAlias('@webroot/') . $params['pic_url']);
                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
                    }
                    $params['pic_url'] = $url;
                } else {
                    unset($params['pic_url']);
                }
            }
            $array = $model->do_update($where, $params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new FlashSaleGroupModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionAll() {
//        if (yii::$app->request->isGet) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->get(); //获取地址栏参数          
//            $model = new \app\models\merchant\system\MerchantComboAccessModel();
//            if (isset($params['searchName'])) {
//                if ($params['searchName'] != "") {
//                    $params['name'] = ['like', "{$params['searchName']}"];
//                }
//                unset($params['searchName']);
//            }
//            if (isset($params['type'])) {
//                if ($params['type'] == "") {
//                    unset($params['type']);
//                }
//            }
//            $params['order_sn'] = ['<>', ""];
//            $params['field'] = "system_merchant_combo_access.*,merchant_user.name as merchant_name,system_merchant_combo.name as combo_name,system_merchant_combo.money as combo_money,system_merchant_combo.pic_url as combo_pic_url ";
//            $params['join'][] = ['inner join', 'merchant_user', 'merchant_user.id = system_merchant_combo_access.merchant_id'];
//            $params['join'][] = ['inner join', 'system_merchant_combo', 'system_merchant_combo.id = system_merchant_combo_access.combo_id'];
//            $array = $model->do_select($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }
//
//    //平台为商户手动添加数据
//    public function actionInsert() {
//        if (yii::$app->request->isPost) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $comboAccess = new \app\models\merchant\system\MerchantComboAccessModel();
//            $res = $comboAccess->do_select(['combo_id' => $params['id']]);
//            if ($res['status'] == 200) {
//                if ($combo['data']['number'] <= count($res['data'])) {
//                    return result(500, '您已超过改套餐的购买次数');
//                }
//            }
//            if ($res['status'] == 500) {
//                return $res;
//            }
//
//
//            $order = "combo_" . date("YmdHis", time()) . rand(1000, 9999);
//            $comboData = array(
//                'merchant_id' => $params['merchant_id'],
//                'order_sn' => $order,
//                'combo_id' => $params['id'],
//                'sms_number' => $combo['data']['sms_number'],
//                'order_number' => $combo['data']['order_number'],
//                'sms_remain_number' => $combo['data']['sms_number'],
//                'order_remain_number' => $combo['data']['order_number'],
//                'validity_time' => $combo['data']['order_number'],
//                'type' => $combo['data']['type'],
//                'status' => 0,
//            );
//            $res = $comboAccess->do_add($comboData);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    /**
     * 修改秒杀shop_flash_sale_group 表的状态
     * @param $id
     * @return array
     * @throws yii\db\Exception
     */
    public function actionUpdateshopflashsalegroup($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            if(!$id || !isset($params['status'])){
                return result(404, "参数错误");
            }
            $model = new FlashSaleGroupModel();
            $where['id'] = $id;
            $data['status'] = $params['status'];
            $tr = Yii::$app->db->beginTransaction();
            try {
                // 修改group 表的状态
                $res = $model->do_update($where, $data);
                if ($res['status'] == 200) {
                    $flash_sale_model = new FlashSaleModel();
                    $where_group['flash_sale_group_id'] = $id;
                    $data['status'] = $params['status'];
                    $array = $flash_sale_model->do_update($where_group, $data); // 修改子表状态
                    if ($array['status'] != 200) {
                        $tr->rollBack();
                        return result(500, "修改失败");
                    }
                    if ($array['status'] == 200){
                        //添加操作记录
                        $operationRecordModel = new OperationRecordModel();
                        $operationRecordData['key'] = $params['key'];
                        $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                        $operationRecordData['operation_type'] = '更新';
                        $operationRecordData['operation_id'] = $id;
                        $operationRecordData['module_name'] = '秒杀';
                        $operationRecordModel->do_add($operationRecordData);
                    }
                    $tr->commit();
                    return result(200, "修改成功");
                } else {
                    $tr->rollBack();
                    return result(500, "请求失败");
                }
            } catch (\Exception $e) {
                $tr->rollBack();
                return result(500, "请求异常");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }
}
