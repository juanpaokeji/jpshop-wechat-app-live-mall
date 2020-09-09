<?php

namespace app\controllers\shop;

use app\models\admin\system\SystemSmsModel;
use app\models\core\SMS\SMS;
use app\models\core\TableModel;
use app\models\merchant\app\AppAccessModel;
use app\models\merchant\distribution\AgentModel;
use app\models\merchant\distribution\DistributionAccessModel;
use app\models\merchant\distribution\OperatorModel;
use app\models\merchant\distribution\SuperModel;
use app\models\merchant\vip\UnpaidVipModel;
use app\models\shop\GroupOrderModel;
use app\models\shop\OrderModel;
use app\models\shop\SignModel;
use app\models\shop\TuanLeaderModel;
use app\models\shop\UserModel;
use app\models\system\SystemAreaModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateAccessModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateModel;
use app\models\system\SystemSmsTemplateAccessModel;
use app\models\system\SystemSmsTemplateIdModel;
use app\models\tuan\LeaderModel;
use Qcloud\Sms\SmsSingleSender;
use yii;
use yii\web\ShopController;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TuanController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                //            'except' => ['order'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionExpress($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            unset($params['id']);
            $info = TuanLeaderModel::instance()->get_info2(['uid' => $id], 'is_self,tuan_express_fee,is_tuan_express');
            if (empty($info)) {
                return result(404, "没有该信息");
            }
            $info['is_self'] = intval($info['is_self']);
            $info['is_tuan_express'] = intval($info['is_tuan_express']);
            return result(200, '获取成功', $info);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 获取团长信息
     * @return array
     */
    public function actionLeader()
    {
        if (yii::$app->request->isGet) {
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['uid'] = yii::$app->session['user_id'];

            $leaderModel = new \app\models\tuan\LeaderModel();
            $info = $leaderModel->do_one($data);
            if ($info['status'] == 200) {
                $area_model = new SystemAreaModel();
                $pro_info = $area_model->do_one(['code' => $info['data']['province_code'], 'field' => 'name']);
                $city_info = $area_model->do_one(['code' => $info['data']['city_code'], 'field' => 'name']);
                $area_info = $area_model->do_one(['code' => $info['data']['area_code'], 'field' => 'name']);
                $info['data']['provice_name'] = isset($pro_info['data']['name']) ? $pro_info['data']['name'] : '';
                $info['data']['city_name'] = isset($city_info['data']['name']) ? $city_info['data']['name'] : '';
                $info['data']['area_names'] = isset($area_info['data']['name']) ? $area_info['data']['name'] : '';
                $user_model = new UserModel();
                $userData = $user_model->find(['id' => yii::$app->session['user_id'], 'field' => 'phone']);
                $info['data']['phone'] = isset($userData['data']['phone']) ? $userData['data']['phone'] : '';
            }
            return $info;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOrder($id)
    {
        if (yii::$app->request->isGet) {
            if (empty($id)) {
                return result(500, "参数错误");
            }
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new \app\models\shop\OrderModel();
            $model->timeOutOrder();
            //$id = yii::$app->session['user_id'];
            //  $id = 146;
            $must = ['type'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            if ($params['type'] == 2) {
                $params["leader_uid"] = $id;
            } else if ($params['type'] == 1) {
                $params["leader_self_uid"] = $id;
            } else {
                return result(500, "参数错误");
            }
            unset($params['id']);
            if (isset($params['text'])) {
                if (trim($params['text']) == "") {
                    unset($params['text']);
                }
            }
            $array = $model->tuanOrder($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 团长核销
     */
    public function actionConfirm()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $orderModel = new \app\models\shop\OrderModel();

            if (!isset($params['order_sn']) && !isset($params['pick_up_code'])){
                return result(500, "参数有误,订单号");
            }

            if (isset($params['order_sn'])){
                $data['order_sn'] = $params['order_sn'];
            }
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['leader_uid'] = yii::$app->session['user_id'];
//            yii::$app->session['user_id'] = 444;
//            yii::$app->session['merchant_id'] = 13;
//            yii::$app->session['key'] = 'ccvWPn';
//            $params['order_sn'] = "202003311854181672";
//            $data['order_sn'] = "202003311854181672";
//            $data['`key`'] = "ccvWPn";
//            $data['merchant_id'] = 13;
//            $data['leader_uid'] = "444";
            $data['status'] = 3;
            // $data['tuan_status'] = 2;
            if (isset($params['pick_up_code'])){
                $data['pick_up_code'] = $params['pick_up_code'];
            }
            $array = $orderModel->queryOrder($data);
            if ($array['status'] != 200) {
                return $array;
            }

            if (!isset($params['order_sn'])){
                $params['order_sn'] = $array['data']['order_sn'];
                $data['order_sn'] = $array['data']['order_sn'];
            }

            $data['status'] = 6;
            unset($data['leader_self_uid']);
            $array = $orderModel->update($data);
            $data1['order_group_sn'] = $params['order_sn'];
            $data1['`key`'] = yii::$app->session['key'];
            $data1['merchant_id'] = yii::$app->session['merchant_id'];
            $data['leader_uid'] = yii::$app->session['user_id'];
            $orderModel = new \app\models\shop\SubOrderModel();
            $array = $orderModel->update($data1);

            //vip权益
            $sql = "select is_vip,vip_validity_time from shop_user where id = " . yii::$app->session['user_id'];
            $vipUser = $orderModel->querySql($sql);
            $vip = 1;
            $appAccessModel = new AppAccessModel();
            $appInfo = $appAccessModel->find(['key' => yii::$app->session['key']]);
            if ($appInfo['status'] == 200 && $appInfo['data']['user_vip'] != 0) {
                if ($appInfo['data']['user_vip'] == 2) {
                    //积分会员等级
                    $vip = 1;
                } else {
                    if ($vipUser[0]['is_vip'] == 1 && $vipUser[0]['vip_validity_time'] > time()) {
                        $sql = "select score_times from shop_vip_config where merchant_id = " . yii::$app->session['merchant_id'] . " and `key` = '" . yii::$app->session['key'] . "'";
                        $vipConfig = $orderModel->querySql($sql);
                        if (count($vipConfig) != 0) {
                            $vip = $vipConfig[0]['score_times'];
                        }
                    }
                }
            }
            $rs = $orderModel->tableSingle("shop_order_group", ['order_sn' => $params['order_sn'], 'delete_time is null' => null]);
            $scoreModel = new \app\models\shop\ScoreModel();
            $scoreData = array(
                '`key`' => yii::$app->session['key'],
                'merchant_id' => yii::$app->session['merchant_id'],
                'user_id' => yii::$app->session['user_id'],
                'score' => $rs['payment_money'] * $vip,
                'content' => '购买商品送积分',
                'type' => '1',
                'status' => '1'
            );
            $scoreModel->add($scoreData);

            $score = $rs['payment_money'] * $vip;
            $user_id = yii::$app->session['user_id'];
            $userModel = new UserModel();
            $user = $userModel->find(['id' => $user_id]);
            $userModel->update(['id' => $user_id, '`key`' => yii::$app->session['key'], 'total_score' => $user['data']['total_score'] + $score, 'score' => $user['data']['score'] + $score]);


            if ($array['status'] == 200) {
                $configModel = new \app\models\tuan\ConfigModel();
                $config = $configModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);
                if ($config['status'] == 200 && $config['data']['status'] == 1) {
                    //团长佣金
                    $balanceModel = new \app\models\shop\BalanceModel();
                    $balance = $balanceModel->do_one(['order_sn' => $params['order_sn'], 'uid' => yii::$app->session['user_id'],'status'=>1, 'type' => 1, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
                    if ($balance['status'] == 200) {
                        $userModel = new UserModel();
                        $user = $userModel->find(['id' => $balance['data']['uid']]);
                        $userModel->update(['id' => $balance['data']['uid'], '`key`' => yii::$app->session['key'], 'balance' => (float)$user['data']['balance'] + (float)$balance['data']['money']]);
                    }
                    //团长配送
                    $balanceModel = new \app\models\shop\BalanceModel();
                    $balance = $balanceModel->do_one(['order_sn' => $params['order_sn'], 'uid' => yii::$app->session['user_id'], 'type' => 6, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
                    if ($balance['status'] == 200) {
                        $userModel = new UserModel();
                        $user = $userModel->find(['id' => $balance['data']['uid']]);
                        $userModel->update(['id' => $balance['data']['uid'], '`key`' => yii::$app->session['key'], 'balance' => (float)$user['data']['balance'] + (float)$balance['data']['money'], 'leader_exp' => $user['data']['leader_exp'] + (int)$balance['data']['money']]);
                        $balanceModel->do_update(['order_sn' => $params['order_sn'], 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']], ['status' => 1]);
                    }

                    //门店佣金
                    if ($rs['data']['supplier_id'] != 0) {
                        $sql = "select sum(money) as num from shop_user_balance where order_sn = '{$rs['order_sn']}' and type = 1";
                        $tuanbalance = Yii::$app->db->createCommand($sql)->queryOne();
                        $balance = $rs['data']['payment_money'] - $tuanbalance['num'] - $rs['data']['commission'] + $rs['data']['commissions_pool'];
                        $sql = "update system_sub_admin set balance = balance+{$balance} where id = " . $rs['data']['supplier_id'] . " ;";
                        Yii::$app->db->createCommand($sql)->execute();
                    }

                }
                $balanceModel = new \app\models\shop\BalanceModel();
                $balanceModel->do_update(['order_sn' => $data['order_sn']], ['status' => 1]);

                $orderRs = $orderModel->find(['order_sn' => $params['order_sn']]);

                //确认收货后更新团长等级、经验
                if ($orderRs['data']['leader_uid'] != 0) {
                    $this->level($orderRs['data']['leader_uid'], floor($orderRs['data']['payment_money']));
                }

                //用户确认收货后，查询普通会员是否可以升级为超级会员
                $appInfo = $appAccessModel->find(['key' => $orderRs['data']['key']]);
                $userModel = new UserModel;
                $userInfo = $userModel->find(['id' => $orderRs['data']['user_id']]);
                //会员等级为普通会员的再做后续判断
                if ($userInfo['status'] == 200 && $userInfo['data']['level'] == 0) {
                    $superModel = new SuperModel();
                    $superInfo = $superModel->one(['key' => $orderRs['data']['key']]);
                    //未查询到超级会员设置信息的，不做处理
                    if ($superInfo['status'] == 200) {
                        //用户消费金额达到设定则升级，否则不做处理
                        $groupOrderModel = new GroupOrderModel();
                        $groupOrderWhere['field'] = "sum(payment_money) as money";
                        $groupOrderWhere['user_id'] = $orderRs['data']['user_id'];
                        $groupOrderWhere['or'] = ['or', ['=', 'status', 6], ['=', 'status', 7]];
                        $moneyInfo = $groupOrderModel->one($groupOrderWhere);
                        if ($moneyInfo['status'] == 200 && $moneyInfo['data']['money'] >= $superInfo['data']['condition']) {
                            //是否开启手动审核
                            if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0) {
                                $levelData['id'] = $orderRs['data']['user_id'];
                                $levelData['`key`'] = $orderRs['data']['key'];
                                $levelData['level'] = 1;
                                $levelData['up_level'] = 1;
                                $levelData['reg_time'] = time();
                                $userModel->update($levelData);
                            } else {
                                $levelData['id'] = $orderRs['data']['user_id'];
                                $levelData['`key`'] = $orderRs['data']['key'];
                                $levelData['up_level'] = 1;
                                $levelData['is_check'] = 0;
                                $levelData['reg_time'] = time();
                                $userModel->update($levelData);
                            }
                        } else {
                            file_put_contents(Yii::getAlias('@webroot/') . '/log.text', date('Y-m-d H:i:s') . "分销_普升超_未查询到会员订单信息或消费金额未达标" . PHP_EOL, FILE_APPEND);
                        }
                    } else {
                        file_put_contents(Yii::getAlias('@webroot/') . '/log.text', date('Y-m-d H:i:s') . "分销_普升超_未查询到超级会员设置信息" . PHP_EOL, FILE_APPEND);
                    }
                }
                //判断父级是否可以升级
                if ($userInfo['status'] == 200 && !empty($userInfo['data']['parent_id'])) {
                    $parentInfo = $userModel->find(['id' => $userInfo['data']['parent_id']]);
                    $sql = "SELECT sum(sog.payment_money) as total FROM `shop_user` su RIGHT JOIN `shop_order_group` sog ON sog.user_id = su.id WHERE su.parent_id = {$userInfo['data']['parent_id']} AND (sog.status = 6 OR sog.status = 7)";
                    $total = $userModel->querySql($sql); //$total[0]['total']
                    if (isset($parentInfo['data'])) {
                        $fanNum = $parentInfo['data']['fan_number'];
                        $secondhandFanNum = $parentInfo['data']['secondhand_fan_number'];
                        $level = $parentInfo['data']['level'];
                        $agentModel = new AgentModel();
                        $agentWhere['key'] = $orderRs['data']['key'];
                        $agentWhere['merchant_id'] = $orderRs['data']['merchant_id'];
                        $agentWhere['status'] = 1;
                        $agentWhere['limit'] = false;
                        $agentInfo = $agentModel->do_select($agentWhere);
                        if (isset($agentInfo['data'])) {
                            foreach ($agentInfo['data'] as $k => $v) {
                                if ((int)$v['fan_number_buy'] <= $total[0]['total'] && $v['fan_number'] <= $fanNum && $v['secondhand_fan_number'] <= $secondhandFanNum) {
                                    $level = 2;
                                    $levelId = $v['id'];
                                }
                            }
                        }
                        $operatorModel = new OperatorModel();
                        $operatorWhere['key'] = $orderRs['data']['key'];
                        $operatorWhere['merchant_id'] = $orderRs['data']['merchant_id'];
                        $operatorWhere['status'] = 1;
                        $operatorWhere['limit'] = false;
                        $operatorInfo = $operatorModel->do_select($operatorWhere);
                        if (isset($operatorInfo['data'])) {
                            foreach ($operatorInfo['data'] as $k => $v) {
                                if ((int)$v['fan_number_buy'] <= $total[0]['total'] && $v['fan_number'] <= $fanNum && $v['secondhand_fan_number'] <= $secondhandFanNum) {
                                    $level = 3;
                                    $levelId = $v['id'];
                                }
                            }
                        }
                        //是否开启手动审核
                        if ($level > $parentInfo['data']['level'] || ($level == $parentInfo['data']['level'] && $levelId != $parentInfo['data']['level_id'])) {
                            $levelData['id'] = $userInfo['data']['parent_id'];
                            $levelData['`key`'] = $orderRs['data']['key'];
                            $levelData['up_level'] = $level;
                            $levelData['reg_time'] = time();
                            if (isset($levelId)) {
                                $levelData['up_level_id'] = $levelId;
                            }
                            if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0) {
                                $levelData['level'] = $level;
                                if (isset($levelId)) {
                                    $levelData['level_id'] = $levelId;
                                }
                            } else {
                                $levelData['is_check'] = 0;
                            }
                            $userModel->update($levelData);
                        }
                    }
                }

                //确认收货后,将每个人的预估分销佣金计入可提现分销佣金中,将订单表中未分配完的佣金计入应用表未分配佣金池
                $userModel = new UserModel();
                $distributionAccessModel = new DistributionAccessModel();
                $accessWhere['key'] = yii::$app->session['key'];
                $accessWhere['merchant_id'] = yii::$app->session['merchant_id'];
                $accessWhere['order_sn'] = $params['order_sn'];
                $accessWhere['or'] = ['or', ['=', 'type', 1], ['=', 'type', 2], ['=', 'type', 3]]; //订单提佣
                $accessWhere['limit'] = false;
                $distributionAccess = $distributionAccessModel->do_select($accessWhere);
                if ($distributionAccess['status'] == 200) {
                    foreach ($distributionAccess['data'] as $k => $v) {
                        $userInfo = $userModel->find(['id' => $v['uid']]);
                        if ($userInfo['status'] == 200) {
                            $userData['id'] = $v['uid'];
                            $userData['`key`'] = $v['key'];
                            $userData['commission'] = $userInfo['data']['commission'] - $v['money'];
                            $userData['withdrawable_commission'] = $v['money'] + $userInfo['data']['withdrawable_commission'];
                            $userModel->update($userData);
                        }
                    }
                }
                $appData = [];
                $appData['`key`'] = $userInfo['data']['key'];
                $appData['merchant_id'] = $userInfo['data']['merchant_id'];
                $appData['commissions_pool'] = $orderRs['data']['commissions_pool'] + $appInfo['data']['commissions_pool'];
                $appAccessModel->update($appData);

                return result(200, "订单{$data['order_sn']}核销成功！");
            } else {
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function level($leader_uid, $exp)
    {
        $table = new TableModel();
        $sql = "select * from shop_user where id = " . $leader_uid;
        $user = $table->querySql($sql);
        if (count($user) > 0) {
            $user[0]['leader_exp'] = $exp + $user[0]['leader_exp'];

            $sql = "select * from shop_leader_level  where min_exp < {$user[0]['leader_exp']} and `key`='ccvWPn'  order by min_exp desc limit 1";
            $res = $table->querySql($sql);
            if (count($res) > 0) {
                $sql = "update shop_user set leader_level = {$res[0]['id']},leader_exp = {$user[0]['leader_exp']} where id ={$leader_uid}";
                Yii::$app->db->createCommand($sql)->execute();
            }else{
                $sql = "update shop_user set leader_exp = {$user[0]['leader_exp']} where id ={$leader_uid}";
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
    }

    /**
     * 团长确认收货
     * @return array
     */
    public function actionTuanOrderReceiving()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $orderModel = new \app\models\shop\OrderModel();
            $data['order_sn'] = $params['order_sn'];
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['leader_self_uid'] = yii::$app->session['user_id'];
            $array = $orderModel->queryOrder($data);
            if ($array['status'] != 200) {
                return result(500, "未找到订单号");
            }
            unset($data['leader_self_uid']);
            $data['tuan_status'] = 2;
            $array = $orderModel->update($data);
            if ($array['status'] == 200) {
                $orderModel = new OrderModel();
                $orderRs = $orderModel->find(['order_sn' => $params['order_sn']]);

                $shopUserModel = new \app\models\shop\UserModel();
                $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);

                $tempModel = new \app\models\system\SystemMiniTemplateModel();
                $minitemp = $tempModel->do_one(['id' => 34]);
                //单号,金额,下单时间,物品名称,
                $tempParams = array(
                    'keyword1' => $orderRs['data']['goodsname'],
                    'keyword2' => $orderRs['data']['payment_money'],
                );

                $tempAccess = new SystemMerchantMiniAccessModel();
                $taData = array(
                    'key' => $orderRs['data']['key'],
                    'merchant_id' => $orderRs['data']['merchant_id'],
                    'mini_open_id' => $shopUser['data']['mini_open_id'],
                    'template_id' => 34,
                    'number' => '0',
                    'template_params' => json_encode($tempParams),
                    'template_purpose' => 'order',
                    'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn']}",
                    'status' => '-1',
                );
                $tempAccess->do_add($taData);

                //订阅消息(取货通知)
                $leaderModel = new LeaderModel();
                $leaderInfo = $leaderModel->do_one(['uid'=>yii::$app->session['user_id']]);
                $leaderName = '未知';
                $leaderPhone = 0;
                $address = '未知';
                if ($leaderInfo['status'] == 200){
                    $leaderName = $leaderInfo['data']['realname'];
                    $leaderPhone = $leaderInfo['data']['phone'] == ''? 0 : $leaderInfo['data']['phone'];
                    $address = $leaderInfo['data']['area_name'] . $leaderInfo['data']['addr'];
                    if (mb_strlen($address, 'utf-8') > 20) {
                        $address = mb_substr($address, 0, 17, 'utf-8') . '...'; //商品名超过20个汉字截断
                    } else {
                        $address = $address;
                    }
                }
                $pickUpCode = 0;
                if ($orderRs['data']['pick_up_code'] != null){
                    $pickUpCode = $orderRs['data']['pick_up_code'];
                }
                $subscribeTempModel = new SystemMerchantMiniSubscribeTemplateModel();
                $subscribeTempInfo = $subscribeTempModel->do_one(['template_purpose' => 'pick_up_notice']);
                if ($subscribeTempInfo['status'] == 200){
                    $accessParams = array(
                        'character_string6' => ['value' => $params['order_sn']],  //订单号
                        'name10' => ['value' => $leaderName],  //团长姓名
                        'phone_number11' => ['value' => $leaderPhone],    //联系电话
                        'number12' => ['value' => $pickUpCode],   //取货码
                        'thing2' => ['value' => $address],   //取货地点
                    );
                    $subscribeTempAccessModel = new SystemMerchantMiniSubscribeTemplateAccessModel();
                    $subscribeTempAccessData = array(
                        'key' => $orderRs['data']['key'],
                        'merchant_id' => $orderRs['data']['merchant_id'],
                        'mini_open_id' => $shopUser['data']['mini_open_id'],
                        'template_id' => $subscribeTempInfo['data']['template_id'],
                        'number' => '0',
                        'template_params' => json_encode($accessParams, JSON_UNESCAPED_UNICODE),
                        'template_purpose' => 'pick_up_notice',
                        'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn']}",
                        'status' => '-1',
                    );
                    $subscribeTempAccessModel->do_add($subscribeTempAccessData);
                }else{
                    file_put_contents(Yii::getAlias('@webroot/') . '/log.text', date('Y-m-d H:i:s') . "取货通知_未查询到该订阅消息模板" . PHP_EOL, FILE_APPEND);
                }


                //发送短信给买家
                $smsModel = new SystemSmsModel();
                $smsWhere['status'] = 1;
                $smsInfo = $smsModel->do_one($smsWhere); //查询短信配置
                $templateIdModel = new SystemSmsTemplateIdModel();
                $templateIdInfo = $templateIdModel->do_one([]); //查询短信模板id
                $buyerPhone = $orderRs['data']['phone']; //买家电话
                if ($smsInfo['status'] == 200 && $templateIdInfo['status'] == 200 && isset($buyerPhone)) {
                    $templateConfig = json_decode($templateIdInfo['data']['config'], true);
                    if ($templateConfig[1]['status'] == 'true') {  //下标1为团长收货买家短信提醒
                        $smsAccessModel = new SystemSmsTemplateAccessModel();
                        $smsInfo['data']['config'] = json_decode($smsInfo['data']['config'], true);
                        $sms = new SMS();
                        $sendRes = $sms->sendSms($buyerPhone,$templateConfig[1]['templateId']);
                        if ($sendRes['status'] == 200) {
                            $smsAccessData['phone'] = $buyerPhone;
                            $smsAccessData['template_id'] = $templateConfig[1]['templateId'];
                            $smsAccessData['type'] = 2; //团长收货买家短信提醒
                            $smsAccessModel->do_add($smsAccessData);
                        } else {
                            file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . json_encode($sendRes, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
                        }
                    } else {
                        file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '团长收货买家短信提醒未开启' . PHP_EOL, FILE_APPEND);
                    }
                } else {
                    file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '未查询到买家电话或腾讯云、短信模板配置信息' . PHP_EOL, FILE_APPEND);
                }


                return result(200, "订单{$data['order_sn']}确认收货成功！");
            } else {
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 修改团长信息
     * @param $id
     * @return array
     */
    public function actionUpdateLeader()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获hexiao取 request 对象
            $params = $request->bodyParams; //获取body传参
            if (empty($params) || !is_array($params)) {
                return result(500, "缺少修改参数");
            }
            $where['`key`'] = yii::$app->session['key'];
            $where['merchant_id'] = yii::$app->session['merchant_id'];
            $where['uid'] = yii::$app->session['user_id'];
            if (isset($params['is_self'])) {
                if ($params['is_self'] === true || $params['is_self'] == 'true') {
                    $params['is_self'] = 1;
                } else {
                    $params['is_self'] = 0;
                }
            }
            $model = new LeaderModel();
            $leader_info = $model->do_one($where);
            if ($leader_info['status'] == 500) {
                return result(500, "数据出错了啊");
            }
            foreach ($params as $key => $val) {
                if (!array_key_exists($key, $leader_info['data'])) {
                    unset($params[$key]);
                }
            }

            $res = $model->do_update($where, $params);
            if ($res['status'] == 200) {
                return result(200, "修改成功");
            } else {
                return $res;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionMerbers()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $where['shop_tuan_user.key'] = yii::$app->session['key'];
            $where['shop_tuan_user.merchant_id'] = yii::$app->session['merchant_id'];
            $tuanUserModel = new \app\models\tuan\UserModel();
            $where['shop_tuan_user.leader_uid'] = yii::$app->session['user_id'];
            $where['field'] = "avatar,nickname,money";
            $where['join'][] = ['inner join', 'shop_user', 'shop_user.id=shop_tuan_user.uid'];
            $where['limit'] = false;
            $data = $tuanUserModel->do_select($where);
            return $data;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //一键收货
    public function actionReceipt()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $orderModel = new \app\models\shop\OrderModel();
          //  $data['fields'] = " * ,shop_order.send_out_time ";

            $data['shop_order_group.`key`'] = yii::$app->session['key'];
            $data['shop_order_group.merchant_id'] = yii::$app->session['merchant_id'];
            $data['leader_self_uid'] = yii::$app->session['user_id'];
            $data['join']=" inner join shop_order on shop_order.order_group_sn=shop_order_group.order_sn ";
            $data['shop_order_group.status']= 3;
            $data['tuan_status']=1;
            $data['express_type <> 0'] =null;
            $time1 = strtotime($params['time']);
            $time2 = $time1+(24*3600);
            $data["send_out_time>{$time1} and send_out_time <{$time2}"]=null;
            $params['shop_order_group.delete_time is null'] = null;
            $array = $orderModel->queryOrder($data);
            if ($array['status'] != 200) {
                return result(500, "未找到订单号");
            }

            unset($data['leader_self_uid']);

            if ($array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {

                    $res = $orderModel->update(['order_sn'=>$array['data'][$i]['order_sn'],'tuan_status'=>2]);
                   // var_dump($res);die();
                    $params['order_sn'] = $array['data'][$i]['order_sn'];

                    $orderModel = new OrderModel();
                    $orderRs = $orderModel->find(['order_sn' => $params['order_sn']]);

                    $shopUserModel = new \app\models\shop\UserModel();
                    $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);

                    $tempModel = new \app\models\system\SystemMiniTemplateModel();
                    $minitemp = $tempModel->do_one(['id' => 34]);
                    //单号,金额,下单时间,物品名称,
                    $tempParams = array(
                        'keyword1' => $orderRs['data']['goodsname'],
                        'keyword2' => $orderRs['data']['payment_money'],
                    );

                    $tempAccess = new SystemMerchantMiniAccessModel();
                    $taData = array(
                        'key' => $orderRs['data']['key'],
                        'merchant_id' => $orderRs['data']['merchant_id'],
                        'mini_open_id' => $shopUser['data']['mini_open_id'],
                        'template_id' => 34,
                        'number' => '0',
                        'template_params' => json_encode($tempParams),
                        'template_purpose' => 'order',
                        'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn']}",
                        'status' => '-1',
                    );
                    $tempAccess->do_add($taData);
                    //发送短信给买家
                    $smsModel = new SystemSmsModel();
                    $smsWhere['status'] = 1;
                    $smsInfo = $smsModel->do_one($smsWhere); //查询短信配置
                    $templateIdModel = new SystemSmsTemplateIdModel();
                    $templateIdInfo = $templateIdModel->do_one([]); //查询短信模板id
                    $buyerPhone = $orderRs['data']['phone']; //买家电话
                    if ($smsInfo['status'] == 200 && $templateIdInfo['status'] == 200 && isset($buyerPhone)) {
                        $templateConfig = json_decode($templateIdInfo['data']['config'], true);
                        if ($templateConfig[1]['status'] == 'true') {  //下标1为团长收货买家短信提醒
                            $smsAccessModel = new SystemSmsTemplateAccessModel();
                            $smsInfo['data']['config'] = json_decode($smsInfo['data']['config'], true);
                            $sms = new SMS();
                            $sendRes = $sms->sendSms($buyerPhone,$templateConfig[1]['templateId']);
                            if ($sendRes['status'] == 200) {
                                $smsAccessData['phone'] = $buyerPhone;
                                $smsAccessData['template_id'] = $templateConfig[1]['templateId'];
                                $smsAccessData['type'] = 2; //团长收货买家短信提醒
                                $smsAccessModel->do_add($smsAccessData);
                            } else {
                                file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . json_encode($sendRes, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
                            }
                        } else {
                            file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '团长收货买家短信提醒未开启' . PHP_EOL, FILE_APPEND);
                        }
                    } else {
                        file_put_contents(Yii::getAlias('@webroot/') . '/sms_error.text', date('Y-m-d H:i:s') . '未查询到买家电话或腾讯云、短信模板配置信息' . PHP_EOL, FILE_APPEND);
                    }


                }
                $number = count($array['data']);
                return result(200, "确认收货{$number}个订单");
            } else {
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

}
