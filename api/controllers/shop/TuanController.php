<?php

namespace app\controllers\shop;

use app\models\shop\OrderModel;
use app\models\shop\SignModel;
use app\models\shop\TuanLeaderModel;
use app\models\shop\UserModel;
use app\models\system\SystemAreaModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\tuan\LeaderModel;
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
                'except' => ['order'], //指定控制器不应用到哪些动作
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
            //  $id = yii::$app->session['user_id'];
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
            $data['order_sn'] = $params['order_sn'];
            $data['`key`'] = yii::$app->session['key'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['leader_self_uid'] = yii::$app->session['user_id'];
//            yii::$app->session['user_id'] = 1551;
//            yii::$app->session['merchant_id'] = 253;
//            yii::$app->session['key'] = '000572';
//            $params['order_sn'] = "201908251021259753";
//            $data['order_sn'] = "201908251021259753";
//            $data['`key`'] = "000572";
//            $data['merchant_id'] = 253;
//            $data['leader_self_uid'] = "1551";

            $data['status'] = 3;
            $data['tuan_status'] = 2;

            $array = $orderModel->queryOrder($data);

            if ($array['status'] != 200) {
                return $array;
            }

            $data['status'] = 6;
            unset($data['leader_self_uid']);
            $array = $orderModel->update($data);
            $data1['order_group_sn'] = $params['order_sn'];
            $data1['`key`'] = yii::$app->session['key'];
            $data1['merchant_id'] = yii::$app->session['merchant_id'];
            //$data['leader_self_uid'] = yii::$app->session['user_id'];
            $orderModel = new \app\models\shop\SubOrderModel();
            $array = $orderModel->update($data1);

            //vip权益
            $sql = "select is_vip,vip_validity_time from shop_user where id = " . yii::$app->session['user_id'];
            $vipUser = $orderModel->querySql($sql);
            $vip = 1;
            if ($vipUser[0]['is_vip'] == 1 && $vipUser[0]['vip_validity_time'] > time()) {
                $sql = "select score_times from shop_vip_config where merchant_id = " . yii::$app->session['merchant_id'] . " `key` = '" . yii::$app->session['key'] . "'";
                $vipConfig = $orderModel->querySql($sql);
                if (count($vipConfig) != 0) {
                    $vip = $vipConfig[0]['score_times'];
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
            $userModel->update(['id' => $user_id, '`key`' => yii::$app->session['key'], 'score' => $user['data']['score'] + $score]);


            if ($array['status'] == 200) {

                $configModel = new \app\models\tuan\ConfigModel();
                $bool = array();
                $config = $configModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);
                if ($config['status'] == 200 && $config['data']['status'] == 1) {
                    //团长佣金
                    $balanceModel = new \app\models\shop\BalanceModel();
                    $balance = $balanceModel->do_one(['order_sn' => $params['order_sn'], 'uid' => yii::$app->session['user_id'], 'type' => 1, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
                    if ($balance['status'] == 200) {
                        $userModel = new UserModel();
                        $user = $userModel->find(['id' => $balance['data']['uid']]);
                        $bool[0] = $userModel->update(['id' => $balance['data']['uid'], '`key`' => yii::$app->session['key'], 'balance' => (float)$user['data']['balance'] + (float)$balance['data']['money']]);
                    }
                    //自提佣金
                    $balanceModel = new \app\models\shop\BalanceModel();
                    $balance = $balanceModel->do_one(['order_sn' => $params['order_sn'], 'uid' => yii::$app->session['user_id'], 'type' => 3, 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
                    if ($balance['status'] == 200) {
                        $userModel = new UserModel();
                        $user = $userModel->find(['id' => $balance['data']['uid']]);
                        $userModel->update(['id' => $balance['data']['uid'], '`key`' => yii::$app->session['key'], 'balance' => (float)$user['data']['balance'] + (float)$balance['data']['money'], 'leader_exp' => $user['data']['leader_exp'] + (int)$balance['data']['money']]);
                        $bool[1] = $balanceModel->do_update(['order_sn' => $params['order_sn'], 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']], ['status' => 1]);
                    }
                }
                if ($bool[0]['status'] == 200 && $bool[1]['status'] == 200) {
                    //供应商金额
                    $subBalanceModel = new \app\models\system\SystemSubAdminBalanceModel();
                    $subBalance = $subBalanceModel->do_select(['order_sn' => $params['order_sn']]);
                    if ($subBalance['status'] == 200) {
                        $subBalanceModel->do_update(['order_sn' => $params['order_sn']], ['status' => 1]);
                        for ($i = 0; $i < count($subBalance['data']); $i++) {
                            $subUserModel = new \app\models\merchant\system\UserModel();
                            $sql = "update system_sub_admin set balance = balance+{$subBalance['data'][$i]['money']} where id = {$subBalance['data'][$i]['sub_admin_id']}";
                            $subUserModel->querySql($sql);
                        }
                    }
                }


                $balanceModel = new \app\models\shop\BalanceModel();
                $balanceModel->do_update(['order_sn' => $data['order_sn']], ['status' => 1]);


                $orderModel = new OrderModel();
                $orderRs = $orderModel->find(['order_sn' => $params['order_sn']]);

                $shopUserModel = new \app\models\shop\UserModel();
                $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);
                $leaderUser = $shopUserModel->find(['id' => $orderRs['data']['leader_self_uid']]);

                $leaderModel = new LeaderModel();
                $leader = $leaderModel->do_one(['uid'=>$orderRs['data']['leader_self_uid']]);

                $areaModel = new SystemAreaModel();
                $area = $areaModel->do_one(['code'=>$leader['data']['area_code']]);

                $tempModel = new \app\models\system\SystemMiniTemplateModel();
                $minitemp = $tempModel->do_one(['id' => 32]);
                //订单编号,订单金额,到货时间,领取位置,手机号,团长
                $tempParams = array(
                    'keyword1' => $params['order_sn'],
                    'keyword2' => $orderRs['data']['pay_money'],
                    'keyword3' => date("Y-m-d h:i:sa", time()),
                    'keyword4' => $area['data']['name'].$leader['data']['area_name'].$leader['data']['addr'],
                    'keyword5' => $leaderUser['data']['phone'],
                    'keyword5' => $leader['data']['realname'],
                );

                $tempAccess = new SystemMerchantMiniAccessModel();
                $taData = array(
                    'key' => $orderRs['data']['key'],
                    'merchant_id' => $orderRs['data']['merchant_id'],
                    'mini_open_id' => $shopUser['data']['mini_open_id'],
                    'template_id' => 32,
                    'number' => '0',
                    'template_params' => json_encode($tempParams),
                    'template_purpose' => 'order',
                    'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn']}",
                    'status' => '-1',
                );
                $tempAccess->do_add($taData);

                return result(200, "订单{$data['order_sn']}核销成功！");
            } else {
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
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

    public function actionMerbers(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $where['shop_tuan_user.key'] = yii::$app->session['key'];
            $where['shop_tuan_user.merchant_id'] = yii::$app->session['merchant_id'];
            $tuanUserModel = new \app\models\tuan\UserModel();
            $where['shop_tuan_user.leader_uid'] = yii::$app->session['user_id'];
            $where['field'] = "avatar,nickname,money";
            $where['join'][] = ['inner join','shop_user','shop_user.id=shop_tuan_user.uid'];
            $data = $tuanUserModel->do_select($where);
            return $data;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
