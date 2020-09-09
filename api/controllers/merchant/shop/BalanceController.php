<?php

namespace app\controllers\merchant\shop;

use app\models\core\TableModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\shop\GoodsModel;
use app\models\shop\SubOrderModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\shop\BalanceModel;
use EasyWeChat\Factory;

class BalanceController extends MerchantController
{

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

    public function actionList()
    {
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
                $data['field'] = "shop_user_balance.*,shop_tuan_leader.realname,shop_user.nickname,shop_user.phone,shop_user.avatar,shop_order_group.status as order_status ,shop_order_group.tuan_status as tuan_status ";
                $data['in'] = ['shop_user_balance.type', [1, 6]];
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
            } elseif ($params['type'] == 3) {
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
                $data['shop_user_balance.content'] = '分销佣金提现';
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
                    $array['data'][$i]['confirm_time'] = $array['data'][$i]['confirm_time'] == "" ? "" : date('Y-m-d H:i:s', $array['data'][$i]['confirm_time']);
                    $array['data'][$i]['format_create_time'] = date('Y-m-d H:i:s', $array['data'][$i]['create_time']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAudit($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new BalanceModel();

            if ($params['status'] == 1) {
                $where['id'] = $id;
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['key'] = $params['key'];
                $data['status'] = 1;
                $data['confirm_time'] = time();
                $res = $model->do_one(['id' => $id]);
                if ($res['data']['send_type'] == 1) {
                    if ($params['type'] == 1) {
                        $res = $this->weixin($res['data']['uid'], $params['key'], $res['data']['remain_money'], $res['data']['balance_sn']);
                        if ($res['return_code'] == "SUCCESS") {
                            unset($params['type']);
                            $array = $model->do_update($where, $data);
                        } else {
                            return result(500, '提现失败');
                        }
                    } else {

                        unset($params['type']);
                        $array = $model->do_update($where, $data);
                    }
                } else {

                    $array = $model->do_update($where, $data);
                }

            } else {
                $balance = $model->do_one(['id' => $id, 'merchant_id' => yii::$app->session['uid'], 'key' => $params['key']]);
                $userModel = new \app\models\shop\UserModel();
                $user = $userModel->find(['id' => $balance['data']['uid']]);
                $userModel->update(['balance' => $user['data']['balance'] + $balance['data']['money'], 'id' => $balance['data']['uid'], '`key`' => $params['key']]);
                $where['id'] = $id;
                $where['merchant_id'] = yii::$app->session['uid'];
                $where['key'] = $params['key'];
                $data['status'] = 2;
                $data['confirm_time'] = time();
                $array = $model->do_update($where, $data);
            }
            if ($array['status'] == 200) {
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '佣金提现申请';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function weixin($uid, $key, $money, $balance_sn)
    {
        $config = $this->getSystemConfig($key, "miniprogrampay", 1);
        $userModel = new \app\models\shop\UserModel();
        $user = $userModel->find(['id' => $uid]);
        $app = Factory::payment($config);
        $res = $app->transfer->toBalance([
            'partner_trade_no' => $balance_sn, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => $user['data']['mini_open_id'],
            'check_name' => 'NO_CHECK', // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
            're_user_name' => '', // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => $money * 100, // 企业付款金额，单位为分
            'desc' => '余额提现', // 企业付款操作说明信息。必填
        ]);
        return $res;
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

    public function actionBalance(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $leader_uid = $params['leader_uid'];
            $this->step1($leader_uid);
            $this->step2();
            $this->step3($leader_uid);
            return result(200,'请求成功');
            return $array;
        } else {
            return result(500, "请求方式错误");
        }

    }

    public function step1($leader_uid)
    {
        $table = new TableModel();
        $sql = "update  shop_user set balance = 0 where id = {$leader_uid}";
        Yii::$app->db->createCommand($sql)->execute();
        $sql = "delete from shop_user_balance where (type =1 or type= 6) and uid = {$leader_uid}";
        Yii::$app->db->createCommand($sql)->execute();
        //状态 0=待付款 1=待发货 2=已取消(24小时未支付) 3=已发货 4=已退款 5=退款中 6=待评价 7=已完成(评价后)  8=已删除  9一键退款  11=拼团中
        $sql = "select * from shop_order_group where  (status =1 or status = 3 or status= 6 or status= 7)  and leader_uid = {$leader_uid}";
        $groupOrders = $table->querySql($sql);

        $configModel = new \app\models\tuan\ConfigModel();
        $con = $configModel->do_one(['merchant_id' => 13, 'key' => 'ccvWPn']);
        for ($i = 0; $i < count($groupOrders); $i++) {
            $orderRs['data'] = $groupOrders[$i];

            $this->balance($groupOrders[$i]['order_sn'], $con['data']['commission_leader_ratio'], $con['data']['commission_selfleader_ratio']);
            $orderRs['data'] = $groupOrders[$i];
            if ($orderRs['data']['express_type'] == 2 && $orderRs['data']['express_price'] > 0 && $orderRs['data']['supplier_id'] == 0) {
                $balanceModel = new \app\models\shop\BalanceModel();
                $data['order_sn'] = $orderRs['data']['order_sn'];
                $data['key'] = $orderRs['data']['key'];
                $data['merchant_id'] = $orderRs['data']['merchant_id'];
                $data['money'] = $orderRs['data']['express_price'];
                $data['type'] = 6;
                $data['uid'] = $orderRs['data']['leader_uid'];
                $data['content'] = "配送费佣金";
                $balanceModel->do_add($data);
            }

            $balance = $this->balance($orderRs['data']['order_sn'], $con['data']['commission_leader_ratio'], 0);
            $data = array(
                'uid' => $orderRs['data']['leader_uid'],
                'order_sn' => $orderRs['data']['order_sn'],
                'money' => $balance[0],
                'content' => "团员消费",
                'type' => 1,
                'status' => 0
            );
            $data['key'] = $orderRs['data']['key'];
            $data['merchant_id'] = $orderRs['data']['merchant_id'];
            $balanceModel = new \app\models\shop\BalanceModel();
            $balanceModel->do_add($data);

            $sql = "update shop_order_group set  leader_money = {$balance[0]} where order_sn = {$orderRs['data']['order_sn']} ";
            Yii::$app->db->createCommand($sql)->execute();
        }
    }

    public function step2()
    {
        $table = new TableModel();
        $sql = "select * from shop_order_group where status =6 or status =7 ";
        $res = $table->querySql($sql);

        for ($i = 0; $i < count($res); $i++) {
            $sql = "select * from shop_user_balance where order_sn = '{$res[$i]['order_sn']}' and status = 0";
            $balance = $table->querySql($sql);
            for ($j = 0; $j < count($balance); $j++) {
                $sql = "update shop_user  set balance =balance+{$balance[$j]['money']} where id = {$balance[$j]['uid']};";
                Yii::$app->db->createCommand($sql)->execute();
            }
            $sql = "update shop_user_balance  set status =1 where order_sn = '{$res[$i]['order_sn']}' ";
            Yii::$app->db->createCommand($sql)->execute();
        }
    }



    public function step3($leader_uid)
    {
        $table = new TableModel();
        $sql = "select * from shop_user_balance where type= 2  and status  = 1 and  uid ={$leader_uid}";
        $res = $table->querySql($sql);

        for ($i = 0; $i < count($res); $i++) {
            $sql = "update shop_user set balance = balance-{$res[$i]['money']} where id = {$res[$i]['user_id']}";
            $res = $table->querySql($sql);
        }
    }

    public function balance($order_sn, $commission_leader_ratio, $commission_selfleader_ratio)
    {
        $money[0] = 0;
        $money[1] = 0;
        //根据订单查询子订单
        $orderSubModel = new SubOrderModel();
        $order = $orderSubModel->findall(['order_group_sn' => $order_sn]);
        //循环订单
        $goodsModel = new GoodsModel();
        for ($k = 0; $k < count($order['data']); $k++) {
            // $good = array(); //循环查询商品
            $good = $goodsModel->find(['id' => $order['data'][$k]['goods_id']]);
            // 判断商品是否单独设置佣金
            if ($good['data']['commission_leader_ratio'] != 0) {
                $money[0] = $money[0] + ($order['data'][$k]['payment_money'] * $good['data']['commission_leader_ratio'] / 100);
            } else {
                $money[0] = $money[0] + ($order['data'][$k]['payment_money'] * $commission_leader_ratio / 100);
            }
            if ($good['data']['commission_selfleader_ratio'] != 0) {
                $money[1] = $money[1] + ($order['data'][$k]['payment_money'] * $good['data']['commission_selfleader_ratio'] / 100);
            } else {
                $money[1] = $money[1] + ($order['data'][$k]['payment_money'] * $commission_selfleader_ratio / 100);
            }
        }

        return $money;
    }
}
