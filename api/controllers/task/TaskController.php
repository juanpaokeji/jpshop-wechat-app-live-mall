<?php

namespace app\controllers\task;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\distribution\AgentModel;
use app\models\merchant\distribution\DistributionAccessModel;
use app\models\merchant\distribution\OperatorModel;
use app\models\merchant\distribution\SuperModel;
use app\models\merchant\vip\UnpaidVipModel;
use app\models\shop\GroupOrderModel;
use app\models\shop\OrderModel;
use app\models\shop\ScoreModel;
use app\models\shop\SubOrderModel;
use app\models\shop\UserModel;
use yii;
use yii\db\Exception;
use yii\web\Controller;
use EasyWeChat\Factory;
use app\models\core\TableModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\system\SystemFormModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class TaskController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionIndex()
    {

        $table = new TableModel();
        $sql = "SET @ids := '';";
        $sql .= " UPDATE system_mini_template_access   SET status= 0,number = number+1 WHERE status =-1 AND number <=5 AND ( SELECT @ids := CONCAT_WS(',',id, @ids) );";
        $a = Yii::$app->db->createCommand($sql)->execute();
        $sql = "SELECT @ids;";
        $res = Yii::$app->db->createCommand($sql)->queryOne();
        $ids = explode(",", substr($res['@ids'], 0, strlen($res['@ids']) - 1));

        $model = new SystemMerchantMiniAccessModel();
        $message = $model->do_select(['id' => ['in', $ids]]);
        if ($message['status'] == 204) {
            return $message;
        }
        if ($message['status'] == 500) {
            return $message;
        }
        for ($i = 0; $i < count($message['data']); $i++) {
            $config = $this->getSystemConfig($message['data'][$i]['key'], "miniprogram");
            $openPlatform = Factory::openPlatform($this->config);

            $formModel = new SystemFormModel();
            $form = $formModel->do_one(['mini_open_id' => $message['data'][$i]['mini_open_id'], 'merchant_id' => $message['data'][$i]['merchant_id'], 'key' => $message['data'][$i]['key'], 'status' => 1]);
            if ($form['status'] != 200) {
                return result(500, "请求失败");
            }

            $mtemp = new \app\models\system\SystemMerchantMiniTemplateModel;
            $mmtemp = $mtemp->do_one(['system_mini_template_id' => $message['data'][$i]['template_id']]);

            $rs = $formModel->do_update(['mini_open_id' => $message['data'][$i]['mini_open_id'], 'merchant_id' => $message['data'][$i]['merchant_id'], 'key' => $message['data'][$i]['key']], ['status' => 0]);

            $model = new SystemMerchantMiniAccessModel();
            $model->do_update(['id' => $message['data'][$i]['id']], ['status' => 1]);
            // 代小程序实现业务
            $miniProgram = $openPlatform->miniProgram($config['app_id'], $config['refresh_token']);
            $data = json_decode($message['data'][$i]['template_params'], true);
            $res = $miniProgram->template_message->send([
                'touser' => $message['data'][$i]['mini_open_id'],
                'template_id' => $mmtemp['data']['template_id'],
                'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$data['keyword1']}",
                'form_id' => $form['data']['formid'],
                'data' => $data
            ]);
        }
    }

//    public function actionExp()
//    {
//        $bool = getConfig(date('Y-m-d'));
//        if ($bool) {
//            $day = date('d');
//            if ($day == "01") {
//                setConfig(date('Y-m-d'), true);
//                $sql = "update shop_user set leader_exp = ROUND(leader_exp/2)";
//                yii::$app->db->createCommand($sql)->execute();
//            }
//        }
//    }
//
//    public function actionLevel()
//    {
//        $bool = getConfig(date('Y-m-d'));
//        if ($bool) {
//            $day = date('d');
//            if ($day == "16") {
//                $userModel = new \app\models\shop\UserModel();
//                $users = $userModel->findall(['is_leader' => 1]);
//                $sql = "";
//                $levelModel = new \app\models\merchant\user\LevelModel();
//                $levels = $levelModel->do_select(['orderby' => 'min_exp asc']);
//                for ($i = 0; $i < count($users['data']); $i++) {
//                    $level = 0;
//                    for ($j = 0; $j < count($levels['data']); $j++) {
//                        if ($users['data'][$i]['leader_exp'] >= $levels['data'][$j]['min_exp']) {
//                            $level = $levels['data'][$j]['id'];
//                        }
//                    }
//                    $sql = $sql . " update shop_user set leader_level = " . $level . " where id = " . $users['data'][$i]['id'] . ";";
//                }
//                yii::$app->db->createCommand($sql)->execute();
//            }
//        }
//    }

    //订单状态 自动更新
    public function actionGoods()
    {
        $appModel = new AppAccessModel();
        $apps = $appModel->findall([]);
        $orderModel = new GroupOrderModel();
        $orders = $orderModel->do_select(['limit' => false]);

        $leader_confirm = array();
        $leader_send = array();
        $user_confirm = array();

        //   $leader_confirm_order_sn  =array();
        $leader_send_order_sn = array();
        $user_confirm_order_sn = array();
        for ($i = 0; $i < count($apps['data']); $i++) {
            for ($j = 0; $j < count($orders['data']); $j++) {
                if ($orders['data'][$j]['key'] == $apps['data'][$i]['key']) {

                    if ($apps['data'][$i]['leader_confirm'] != 0) {
                        $time = $apps['data'][$i]['leader_confirm'] * 3600 * 24;
                        if (time() >= $orders['data'][$j]['update_time'] + $time && $orders['data'][$j]['status'] == 3 && $orders['data'][$j]['is_tuan'] == 1 && $orders['data'][$j]['tuan_status'] == 1) {
                            $leader_confirm[] = $orders['data'][$j]['id'];
                            $leader_confirm_order_sn[] = $orders['data'][$j]['order_sn'];
                        }
                    }
                    if ($apps['data'][$i]['leader_send'] != 0) {
                        $time = $apps['data'][$i]['leader_confirm'] * 3600 * 24;
                        if (time() >= $orders['data'][$j]['update_time'] + $time && $orders['data'][$j]['status'] == 3 && $orders['data'][$j]['is_tuan'] == 1 && $orders['data'][$j]['tuan_status'] == 2) {
                            $leader_send[] = $orders['data'][$j]['id'];
                            $leader_send_order_sn[] = $orders['data'][$j]['order_sn'];
                        }
                    }
                    if ($apps['data'][$i]['user_confirm'] != 0) {
                        $time = $apps['data'][$i]['leader_confirm'] * 3600 * 24;

                        if (time() >= $orders['data'][$j]['update_time'] + $time && $orders['data'][$j]['status'] == 3 && $orders['data'][$j]['express_type'] == 0) {

                            $user_confirm[] = $orders['data'][$j]['id'];
                            $user_confirm_order_sn[] = $orders['data'][$j]['order_sn'];
                        }
                    }
                }
            }

        }

        $res1 = $orderModel->do_update(['id' => $leader_confirm], ['tuan_status' => 2]);
        $res2 = $orderModel->do_update(['id' => $leader_send], ['status' => 6]);
        $res3 = $orderModel->do_update(['id' => $user_confirm], ['status' => 6]);
        //  var_dump($res1);var_dump($res2);var_dump($res3);die();
        //团长核销 加积分，团长佣金结算
        for ($i = 0; $i < count($leader_send_order_sn); $i++) {
            $params['order_sn'] = $leader_send_order_sn[$i];
            $orderModel = new \app\models\shop\OrderModel();
            $data['order_sn'] = $params['order_sn'];
            $array = $orderModel->queryOrder($data);

            if ($array['status'] != 200) {
                return $array;
            }

            $data['status'] = 6;
            unset($data['leader_self_uid']);
            $array = $orderModel->update($data);
            $data1['order_group_sn'] = $params['order_sn'];
            $data1['`key`'] = $array['data'][0]['key'];
            $data1['merchant_id'] = $array['data'][0]['merchant_id'];
            //$data['leader_self_uid'] = yii::$app->session['user_id'];
            $orderModel = new \app\models\shop\SubOrderModel();
            $array = $orderModel->update($data1);

            //vip权益
            $sql = "select is_vip,vip_validity_time from shop_user where id = " . $array['data'][0]['user_id'];
            $vipUser = $orderModel->querySql($sql);
            $vip = 1;
            $appInfo = $appModel->find(['key' => $array['data'][0]['key']]);
            if ($appInfo['status'] == 200 && $appInfo['data']['user_vip'] != 0){
                if ($appInfo['data']['user_vip'] == 2){
                    //积分会员等级
                    $userModel = new UserModel;
                    $userInfo = $userModel->find(['id' => $array['data'][0]['user_id']]);
                    $unpaidVipModel = new UnpaidVipModel();
                    $unpaidVipWhere = [];
                    $unpaidVipWhere['key'] = $array['data'][0]['key'];
                    $unpaidVipWhere['merchant_id'] = $array['data'][0]['merchant_id'];
                    $unpaidVipWhere['limit'] = false;
                    $unpaidVipInfo = $unpaidVipModel->do_select($unpaidVipWhere);
                    if ($unpaidVipInfo['status'] == 200 && $userInfo['status'] == 200){
                        $minLev = reset($unpaidVipInfo['data']);//最低等级
                        $maxLev = end($unpaidVipInfo['data']);//最高等级
                        //总积分大于等于最高等级
                        if ($userInfo['data']['total_score'] >= $maxLev['min_score']){
                            $vip = $maxLev['score_times'];
                        }
                        //总积分在最低和最高之间的
                        if ($userInfo['data']['total_score'] >= $minLev['min_score'] && $userInfo['data']['total_score'] < $maxLev['min_score']){
                            foreach ($unpaidVipInfo['data'] as $k=>$v){
                                if ($userInfo['data']['total_score'] >= $v['min_score']){
                                    $vip = $v['score_times'];
                                }
                            }
                        }
                    }
                }else{
                    if ($vipUser[0]['is_vip'] == 1 && $vipUser[0]['vip_validity_time'] > time()) {
                        $sql = "select score_times from shop_vip_config where merchant_id = " . $array['data'][0]['merchant_id'] . " and `key` = '" . $array['data'][0]['key'] . "'";
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
                'merchant_id' => $array['data'][0]['merchant_id'],
                'user_id' => $array['data'][0]['user_id'],
                'score' => $rs['payment_money'] * $vip,
                'content' => '购买商品送积分',
                'type' => '1',
                'status' => '1'
            );
            $scoreModel->add($scoreData);

            $score = $rs['payment_money'] * $vip;
            $user_id = $array['data'][0]['user_id'];
            $userModel = new UserModel();
            $user = $userModel->find(['id' => $user_id]);
            $userModel->update(['id' => $user_id, '`key`' => $array['data'][0]['key'],'total_score' => $user['data']['total_score'] + $score ,'score' => $user['data']['score'] + $score]);

            //确认收货后更新团长等级、经验
            $orderModel = new OrderModel;
            $orderRs = $orderModel->find(['order_sn' => $params['order_sn']]);
            if ($orderRs['data']['leader_uid'] != 0){
                $this->level($orderRs['data']['leader_uid'],floor($orderRs['data']['payment_money']));
            }

        }
        //用户确认收货 加积分，团长佣金结算
        for ($i = 0; $i < count($user_confirm_order_sn); $i++) {
            $model = new OrderModel();
            $params['order_sn'] = $user_confirm_order_sn[$i];
            $data['order_sn'] = $params['order_sn'];
            $array = $orderModel->queryOrder($data);
            if ($array['status'] != 200) {
                return $array;
            }
            $subOrder = new SubOrderModel();
            $sub['`key`'] = $array['data'][0]['key'];
            $sub['merchant_id'] = $array['data'][0]['merchant_id'];
            $sub['user_id'] = $array['data'][0]['user_id'];
            $sub['order_group_sn'] = $params['order_sn'];
            $sub['confirm_time'] = time();
            $subOrder->update($sub);

            //vip权益
            $sql = "select is_vip,vip_validity_time from shop_user where id = " . $array['data'][0]['user_id'];
            $vipUser = $subOrder->querySql($sql);
            $vip = 1;
            $appInfo = $appModel->find(['key' => $array['data'][0]['key']]);
            if ($appInfo['status'] == 200 && $appInfo['data']['user_vip'] != 0){
                if ($appInfo['data']['user_vip'] == 2){
                    //积分会员等级
                    $userModel = new UserModel;
                    $userInfo = $userModel->find(['id' => $array['data'][0]['user_id']]);
                    $unpaidVipModel = new UnpaidVipModel();
                    $unpaidVipWhere = [];
                    $unpaidVipWhere['key'] = $array['data'][0]['key'];
                    $unpaidVipWhere['merchant_id'] = $array['data'][0]['merchant_id'];
                    $unpaidVipWhere['limit'] = false;
                    $unpaidVipInfo = $unpaidVipModel->do_select($unpaidVipWhere);
                    if ($unpaidVipInfo['status'] == 200 && $userInfo['status'] == 200){
                        $minLev = reset($unpaidVipInfo['data']);//最低等级
                        $maxLev = end($unpaidVipInfo['data']);//最高等级
                        //总积分大于等于最高等级
                        if ($userInfo['data']['total_score'] >= $maxLev['min_score']){
                            $vip = $maxLev['score_times'];
                        }
                        //总积分在最低和最高之间的
                        if ($userInfo['data']['total_score'] >= $minLev['min_score'] && $userInfo['data']['total_score'] < $maxLev['min_score']){
                            foreach ($unpaidVipInfo['data'] as $k=>$v){
                                if ($userInfo['data']['total_score'] >= $v['min_score']){
                                    $vip = $v['score_times'];
                                }
                            }
                        }
                    }
                }else{
                    if ($vipUser[0]['is_vip'] == 1 && $vipUser[0]['vip_validity_time'] > time()) {
                        $sql = "select score_times from shop_vip_config where merchant_id = " . $array['data'][0]['merchant_id'] . " and `key` = '" . $array['data'][0]['key'] . "'";
                        $vipConfig = $orderModel->querySql($sql);
                        if (count($vipConfig) != 0) {
                            $vip = $vipConfig[0]['score_times'];
                        }
                    }
                }
            }
            $rs = $model->tableSingle("shop_order_group", ['order_sn' => $params['order_sn'], 'delete_time is null' => null]);
            $scoreModel = new ScoreModel();

            $scoreData = array(
                '`key`' => $array['data'][0]['key'],
                'merchant_id' => $array['data'][0]['merchant_id'],
                'user_id' => $array['data'][0]['user_id'],
                'score' => $rs['payment_money'] * $vip,
                'content' => '购买商品送积分',
                'type' => '1',
                'status' => '1'
            );
            $scoreModel->add($scoreData);


            $score = $rs['payment_money'] * $vip;
            $user_id = $array['data'][0]['user_id'];
            $userModel = new UserModel();
            $user = $userModel->find(['id' => $user_id]);
            $userModel->update(['id' => $user_id, '`key`' => $array['data'][0]['key'],'total_score' => $user['data']['total_score'] + $score ,'score' => $user['data']['score'] + $score]);

            $orderModel = new OrderModel();
            $orderRs = $orderModel->find(['order_sn' => $params['order_sn']]);

            $shopUserModel = new \app\models\shop\UserModel();
            $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);


            //用户确认收货后，查询普通会员是否可以升级为超级会员
            $appAccessModel = new \app\models\merchant\app\AppAccessModel();
            $appInfo = $appAccessModel->find(['key'=>$orderRs['data']['key']]);
            $userModel = new UserModel;
            $userInfo = $userModel->find(['id' => $orderRs['data']['user_id']]);
            //会员等级为普通会员的再做后续判断
            if ($userInfo['status'] == 200 && $userInfo['data']['level'] == 0){
                $superModel = new SuperModel();
                $superInfo = $superModel->one(['key'=>$orderRs['data']['key']]);
                //未查询到超级会员设置信息的，不做处理
                if ($superInfo['status'] == 200){
                    //用户消费金额达到设定则升级，否则不做处理
                    $groupOrderModel = new GroupOrderModel();
                    $groupOrderWhere['field'] =  "sum(payment_money) as money";
                    $groupOrderWhere['user_id'] = $orderRs['data']['user_id'];
                    $groupOrderWhere['or'] = ['or',['=','status', 6],['=','status', 7]];
                    $moneyInfo = $groupOrderModel->one($groupOrderWhere);
                    if ($moneyInfo['status'] == 200 && $moneyInfo['data']['money'] >= $superInfo['data']['condition']){
                        //是否开启手动审核
                        if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0){
                            $levelData['id'] = $orderRs['data']['user_id'];
                            $levelData['`key`'] = $orderRs['data']['key'];
                            $levelData['level'] = 1;
                            $levelData['up_level'] = 1;
                            $levelData['reg_time'] = time();
                            $userModel->update($levelData);
                        }else{
                            $levelData['id'] = $orderRs['data']['user_id'];
                            $levelData['`key`'] = $orderRs['data']['key'];
                            $levelData['up_level'] = 1;
                            $levelData['is_check'] = 0;
                            $levelData['reg_time'] = time();
                            $userModel->update($levelData);
                        }
                    }
                }
            }
            //判断父级是否可以升级
            if ($userInfo['status'] == 200 && !empty($userInfo['data']['parent_id'])){
                $parentInfo = $userModel->find(['id'=>$userInfo['data']['parent_id']]);
                $sql = "SELECT sum(sog.payment_money) as total FROM `shop_user` su RIGHT JOIN `shop_order_group` sog ON sog.user_id = su.id WHERE su.parent_id = {$userInfo['data']['parent_id']} AND (sog.status = 6 OR sog.status = 7)";
                $total = $userModel->querySql($sql); //$total[0]['total']
                if (isset($parentInfo['data'])){
                    $fanNum = $parentInfo['data']['fan_number'];
                    $secondhandFanNum = $parentInfo['data']['secondhand_fan_number'];
                    $level = $parentInfo['data']['level'];
                    $agentModel = new AgentModel();
                    $agentWhere['key'] = $orderRs['data']['key'];
                    $agentWhere['merchant_id'] = $orderRs['data']['merchant_id'];
                    $agentWhere['status'] = 1;
                    $agentWhere['limit'] = false;
                    $agentInfo = $agentModel->do_select($agentWhere);
                    if (isset($agentInfo['data'])){
                        foreach ($agentInfo['data'] as $k=>$v){
                            if ($v['fan_number_buy'] <= $total[0]['total'] && $v['fan_number'] <= $fanNum && $v['secondhand_fan_number'] <= $secondhandFanNum){
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
                    if (isset($operatorInfo['data'])){
                        foreach ($operatorInfo['data'] as $k=>$v){
                            if ($v['fan_number_buy'] <= $total[0]['total'] && $v['fan_number'] <= $fanNum && $v['secondhand_fan_number'] <= $secondhandFanNum){
                                $level = 3;
                                $levelId = $v['id'];
                            }
                        }
                    }
                    //是否开启手动审核
                    if ($level > $parentInfo['data']['level'] || ($level == $parentInfo['data']['level'] && $levelId != $parentInfo['data']['level_id'])){
                        $levelData['id'] = $userInfo['data']['parent_id'];
                        $levelData['`key`'] = $orderRs['data']['key'];
                        $levelData['up_level'] = $level;
                        $levelData['reg_time'] = time();
                        if (isset($levelId)){
                            $levelData['up_level_id'] = $levelId;
                        }
                        if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0){
                            $levelData['level'] = $level;
                            if (isset($levelId)){
                                $levelData['level_id'] = $levelId;
                            }
                        }else{
                            $levelData['is_check'] = 0;
                        }
                        $userModel->update($levelData);
                    }
                }
            }


            //确认收货后,将每个人的预估分销佣金计入可提现分销佣金中,将订单表中未分配完的佣金计入应用表未分配佣金池
            $userModel = new UserModel();
            $distributionAccessModel = new DistributionAccessModel();
            $accessWhere['key'] = $orderRs['data']['key'];
            $accessWhere['merchant_id'] = $orderRs['data']['merchant_id'];
            $accessWhere['order_sn'] = $params['order_sn'];
            $accessWhere['type'] = 1; //订单提佣
            $accessWhere['limit'] = false;
            $distributionAccess = $distributionAccessModel->do_select($accessWhere);
            if ($distributionAccess['status'] == 200){
                foreach ($distributionAccess['data'] as $k=>$v){
                    $userInfo = $userModel->find(['id'=>$v['uid']]);
                    if ($userInfo['status'] == 200){
                        $userData['id'] = $v['uid'];
                        $userData['`key`'] = $v['key'];
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
            
        }
        $this->test();

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
                $sql = "update shop_user set leader_level = {$res[0]['id']},leader_exp = {$user[0]['leader_exp']}";
                Yii::$app->db->createCommand($sql)->execute();
            }else{
                $sql = "update shop_user set leader_exp = {$user[0]['leader_exp']}";
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
    }

    /**
     * 订单关闭
     */

    public function actionOrderClose()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
//            $orderModel = new  GroupOrderModel();
//            $orderModel->do_select(['status' => 0, 'create_time' =>]);
            $table = new TableModel();
            $time = time();
            $sql = "select * from shop_order_group  where status = 0 and  create_time+(24*60*60)<={$time} and is_send_message = 0";
            $res = $table->querySql($sql);

            if (count($res) != 0) {
                $order_sn = "";
                for ($i = 0; $i < count($res); $i++) {
                    if ($i == 0) {
                        $order_sn = $res[$i]['order_sn'];
                    } else {
                        $order_sn = $order_sn . "," . $res[$i]['order_sn'];
                    }
                }
                $sql = "update shop_order_group set is_send_message = 1 where order_sn in ($order_sn)";
                Yii::$app->db->createCommand($sql)->execute();
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public  function actionDelOrder(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $time = time();
            $sql = " delete  from shop_order_group where status = 2 and create_time+(48*60*60)<={$time}";
            Yii::$app->db->createCommand($sql)->execute();
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function test()
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
}
