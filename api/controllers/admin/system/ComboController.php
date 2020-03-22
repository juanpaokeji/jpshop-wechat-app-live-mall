<?php

namespace app\controllers\admin\system;

use yii;
use yii\web\CommonController;
use app\models\merchant\system\MerchantComboModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;

class ComboController extends CommonController {

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
            $model = new MerchantComboModel();
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
            $params['<>'] = ['type', 9];
            $array = $model->do_select($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAlls() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数      
            $model = new \app\models\merchant\system\MerchantComboAccessModel();
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['name'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }

            $params['system_merchant_combo_access.status'] = 1;
            $params['field'] = "system_merchant_combo_access.*,system_merchant_combo.name as combo_name,merchant_user.phone as merchant_phone,system_app_access.name as app_name";
            $params['join'][] = ['inner join', 'system_merchant_combo', 'system_merchant_combo.id = system_merchant_combo_access.combo_id'];
            $params['join'][] = ['inner join', 'merchant_user', 'merchant_user.id = system_merchant_combo_access.merchant_id'];
            $params['join'][] = ['left join', 'system_app_access', 'system_app_access.`key` = system_merchant_combo_access.`key`'];
            $array = $model->do_select($params);
            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['format_validity_time'] = date('Y-m-d', $array['data'][$i]['validity_time']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id) {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new MerchantComboModel();
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
            $model = new MerchantComboModel();
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
            $model = new MerchantComboModel();
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
            $model = new MerchantComboModel();
            $params['id'] = $id;
            $array = $model->do_delete($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAll() {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数          
            $model = new \app\models\merchant\system\MerchantComboAccessModel();
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
            $params['<>'] = ['order_sn', ""];
            $params['field'] = "system_merchant_combo_access.*,merchant_user.phone as merchant_phone,system_merchant_combo.name as combo_name,system_merchant_combo.money as combo_money,system_merchant_combo.pic_url as combo_pic_url ";
            $params['join'][] = ['inner join', 'merchant_user', 'merchant_user.id = system_merchant_combo_access.merchant_id'];
            $params['join'][] = ['inner join', 'system_merchant_combo', 'system_merchant_combo.id = system_merchant_combo_access.combo_id'];
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //平台为商户手动添加数据
    public function actionInsert() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new MerchantComboModel();
            $combo_id = 0;
            $res = $model->do_one(['type' => 9]);

            if ($res['status'] == 204) {
                $array = array(
                    'name' => "平台添加",
                    'pic_url' => "",
                    'sms_number' => 0,
                    'order_number' => 0,
                    'money' => 0,
                    'validity_time' => 12,
                    'detail_info' => "平台添加",
                    'type' => 9,
                    'number' => 0,
                );
                $res = $model->do_add($array);
                $combo_id = $res['data'];
            } else {
                $combo_id = $res['data']['id'];
            }
            if (isset($params['combo_id'])) {
                if ($params['combo_id'] != 0) {
                    $combo_id = $params['combo_id'];
                } else {
                    $combo_id = 9;
                }
            } else {
                $combo_id = 9;
            }

            $comboAccess = new \app\models\merchant\system\MerchantComboAccessModel();
//            $res = $comboAccess->do_select(['combo_id' => $combo_id]);
////            if ($res['status'] == 200) {
////                if ($res['data']['number'] != 0) {
////                    if ($res['data']['number'] <= count($res['data'])) {
////                        return result(500, '您已超过改套餐的购买次数');
////                    }
////                }
////            }
//            if ($res['status'] == 500) {
//                return $res;
//            }
            $type = 9;
            if (isset($params['type'])) {
                if ($params['type'] != 0) {
                    $type = $params['type'];
                }
            }
            $order = "combo_" . date("YmdHis", time()) . rand(1000, 9999);
            $comboData = array(
                'key' => $params['key'],
                'merchant_id' => $params['merchant_id'],
                'order_sn' => $order,
                'combo_id' => $combo_id,
                'sms_number' => $params['sms_number'],
                'order_number' => $params['order_number'],
                'sms_remain_number' => $params['sms_number'],
                'order_remain_number' => $params['order_number'],
                'validity_time' => isset($params['validity_time']) ? strtotime($params['validity_time']) : strtotime(date('Y-m-d', strtotime("+ 12month"))),
                'remarks' => "平台添加",
                'type' => $type,
                'status' => 1,
            );
            $res = $comboAccess->do_add($comboData);
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
