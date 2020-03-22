<?php

namespace app\controllers\merchant\shop;

use app\models\merchant\system\OperationRecordModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\shop\BalanceModel;

class BalanceController extends MerchantController {

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

            $must = ['key', 'type'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new BalanceModel();
            $data['shop_user_balance.merchant_id'] = yii::$app->session['uid'];
            $data['shop_user_balance.key'] = $params['key'];
            if (isset($params['page'])) {
                $data['page'] = $params['page'];
            }
            if (isset($params['limit'])) {
                $data['limit'] = $params['limit'];
            }
            if ($params['type'] == 1) {


                $data['field'] = "shop_user_balance.*,shop_tuan_leader.realname,shop_user.phone,shop_user.avatar,shop_order_group.status as order_status ,shop_order_group.tuan_status as tuan_status ";
                $data['<>'] = ['shop_user_balance.type', 0];
                $data['join'][] = ['inner join', 'shop_order_group', 'shop_order_group.order_sn = shop_user_balance.order_sn'];
                $data['join'][] = ['inner join', 'shop_tuan_leader', 'shop_tuan_leader.uid = shop_user_balance.uid'];
                $data['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_user_balance.uid'];
                if (isset($params['searchName'])) {
                    if ($params['searchName'] != "") {
                        $params['shop_tuan_leader.realname'] = ['like', "{$params['searchName']}"];
                    }
                }
                if (isset($params['order_status'])) {
                    if ($params['order_status'] != "") {
                        $data['shop_order_group.status'] = $params['order_status'];
                    }
                }
                if (isset($params['balance_status'])) {
                    if ($params['balance_status'] != "") {
                        $data['shop_user_balance.status'] = $params['balance_status'];
                    }
                }
                if (isset($params['datetime'])) {
                    if ($params['datetime'] != "") {
                        $time = explode(" - ", $params['datetime']);
                        $start_time = strtotime(trim($time[0] . " 00:00:00"));
                        $end_time = strtotime(trim($time[1] . " 23:59:59"));
                        $data['shop_user_balance.create_time'] = [['>=', $start_time], ['<=', $end_time]];
                    }
                }
                if (isset($params['order_sn'])) {
                    if ($params['order_sn'] != "") {
                        $data['shop_user_balance.order_sn'] = $params['order_sn'];
                    }
                }
            } elseif ($params['type'] == 2) {
                $data['field'] = "shop_user_balance.*,shop_user.phone,shop_user.avatar,shop_user.nickname ";
                $data['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_user_balance.uid'];
                if (isset($params['searchName'])) {
                    if ($params['searchName'] != "") {
                        $params['shop_user.nickname'] = ['like', "{$params['searchName']}"];
                    }
                }
                if (isset($params['datetime'])) {
                    if ($params['datetime'] != "") {
                        $time = explode(" - ", $params['datetime']);
                        $start_time = strtotime(trim($time[0] . " 00:00:00"));
                        $end_time = strtotime(trim($time[1] . " 23:59:59"));
                        $data['shop_user_balance.create_time'] = [['>=', $start_time], ['<=', $end_time]];
                    }
                }
                $data['shop_user_balance.type'] = 0;
                $data['order_sn'] = 0;
                if (isset($params['status'])) {
                    if ($params['status'] != "") {
                        if ($params['status'] == 11) {
                            $data['shop_user_balance.status'] = 3;
                            $data['shop_user_balance.status'] = 0;
                        } else if ($params['status'] == 12) {
                            $data['shop_user_balance.status'] = 3;
                            $data['shop_user_balance.status'] = 1;
                        } else {
                            $data['shop_user_balance.status'] = $params['status'];
                        }
                    }
                }
            }

            $array = $model->do_select($data);
            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    $array['data'][$i]['confirm_time'] = date('Y-m-d H:i:s', $array['data'][$i]['confirm_time']);
                    $array['data'][$i]['format_create_time'] = date('Y-m-d H:i:s', $array['data'][$i]['create_time']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAudit($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new BalanceModel();
            if ($params['status'] == 1) {
                $where['id'] = $id;
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['key'] = $params['key'];
                $data['status'] = 1;
                $array = $model->do_update($where, $data);

                $res = $model->do_one(['id' => $id]);
                if ($res['data']['send_type'] == 1) {
                    $this->weixin($res['uid'], $params['key'], $res['remain_money'], $res['balance_sn']);
                }

                //withdraw_fee_ratio
            } else {
                $balance = $model->do_one(['id' => $id, 'merchant_id' => yii::$app->session['uid'], 'key' => $params['key']]);
                $userModel = new \app\models\shop\UserModel();
                $user = $userModel->find(['id' => $balance['data']['uid']]);
                $userModel->update(['balance' => $user['data']['balance'] + $balance['data']['money'], 'id' => $balance['data']['uid'], '`key`' => $params['key']]);
                $where['id'] = $id;
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['key'] = $params['key'];
                $data['status'] = 2;
                $array = $model->do_update($where, $data);
            }
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '佣金体现申请';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function weixin($uid, $key, $money, $balance_sn) {
        $config = $this->getSystemConfig($key, "miniprogrampay", 1);
        $userModel = new \app\models\shop\UserModel();
        $user = $userModel->find(['id' => $uid]);
        $app = Factory::payment($config);
        $app->transfer->toBalance([
            'partner_trade_no' => $balance_sn, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => $user['data']['nimi_open_id'],
            'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
            're_user_name' => '', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => $money * 100, // 企业付款金额，单位为分
            'desc' => '余额提现', // 企业付款操作说明信息。必填
        ]);
    }

//    public function actionPay($id) {
//        $app->transfer->toBalance([
//        'partner_trade_no' =>, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
//        'openid' => 'oxTWIuGaIt6gTKsQRLau2M0yL16E',
//        'check_name' => 'FORCE_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
//        're_user_name' => '王小帅', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
//        'amount' => 10000, // 企业付款金额，单位为分
//        'desc' => '理赔', // 企业付款操作说明信息。必填
//        ]);
//
////查询付款到零钱的订单
//
//        $partnerTradeNo = 1233455;
//        $app->transfer->queryBalanceOrder($partnerTradeNo);
//    }
}
