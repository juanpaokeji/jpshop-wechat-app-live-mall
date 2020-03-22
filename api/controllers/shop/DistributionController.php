<?php
namespace app\controllers\shop;

use app\models\merchant\distribution\AgentModel;
use app\models\merchant\distribution\DistributionAccessModel;
use app\models\merchant\distribution\OperatorModel;
use app\models\shop\OrderModel;
use app\models\shop\UserModel;
use Yii;
use yii\web\ShopController;


class DistributionController extends ShopController{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionIndex(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $userModel = new UserModel();
            $userId = yii::$app->session['user_id'];
            $userInfo = $userModel->find(['id'=>$userId]);
            if ($userInfo['status'] != 200){
                return result(500, "未查询到会员信息");
            }
            //待结算、可提现佣金
            $data['commission'] = $userInfo['data']['commission'];
            $data['withdrawable_commission'] = $userInfo['data']['withdrawable_commission'];

            $agentModel = new AgentModel();
            $agentWhere['key'] = $params['key'];
            $agentWhere['merchant_id'] = yii::$app->session['uid'];
            $agentWhere['status'] = 1;
            $agentWhere['limit'] = false;
            $agentInfo = $agentModel->do_select($agentWhere);  //可用的代理商等级
            $operatorModel = new OperatorModel();
            $operatorWhere['key'] = $params['key'];
            $operatorWhere['merchant_id'] = yii::$app->session['uid'];
            $operatorWhere['status'] = 1;
            $operatorWhere['limit'] = false;
            $operatorInfo = $operatorModel->do_select($operatorWhere);  //可用的运营商等级
            //会员等级
            switch ($userInfo['data']['level']) {
                //普通会员
                case "0":
                    $data['level_name'] = '普通会员';
                    break;
                //超级会员
                case "1":
                    $data['level_name'] = '超级会员';
                    break;
                //代理商
                case "2":
                    foreach ($agentInfo['data'] as $key=>$val){
                        if ($val['id'] == $userInfo['data']['level_id']){
                            $data['level_name'] = $val['name'];
                        }
                    }
                    break;
                //运营商
                case "3":
                    foreach ($operatorInfo['data'] as $key=>$val){
                        if ($val['id'] == $userInfo['data']['level_id']){
                            $data['level_name'] = $val['name'];
                        }
                    }
                    break;
                default:
                    break;
            }

            $model = new DistributionAccessModel();
            $today = strtotime("today"); //当天0时0分0秒的时间戳
            $where['field'] = "sum(shop_distribution_access.money) as total,count(shop_distribution_access.order_sn) as num";
            $where['join'][] = ['left join', 'shop_order_group', 'shop_distribution_access.order_sn = shop_order_group.order_sn'];
            $where['shop_distribution_access.key'] = yii::$app->session['key'];
            $where['shop_distribution_access.merchant_id'] = yii::$app->session['merchant_id'];
            $where['shop_distribution_access.uid'] = $userId;
            $where['or'] = ['or',['=','shop_distribution_access.type', 1],['=','shop_distribution_access.type', 3]];
            $where['or'] = ['or',['=','shop_order_group.status', 1],['=','shop_order_group.status', 3],['=','shop_order_group.status', 5],['=','shop_order_group.status', 6],['=','shop_order_group.status', 7]];
            $where['limit'] = false;

            //当天收益情况
            $todayWhere = $where;
            $todayWhere['>'] = ['shop_distribution_access.create_time',$today];
            $todayInfo = $model->do_select($todayWhere);


            if ($todayInfo['status'] == 200){
                $data['today'] = $todayInfo['data'][0]['total'];
                if ($todayInfo['data'][0]['num'] == 0){
                    $data['today'] = 0;
                }
            } else {
                return $todayInfo;
            }

            //不包含当天,当月收益情况
            $month = mktime(0,0,0,date('m'),1,date('Y')); //获取当月1号0时0分0秒时间戳
            $where['>='] = ['shop_distribution_access.create_time',$month];
            $where['<'] = ['shop_distribution_access.create_time',$today];
            $monthWhere = $where;
            $monthWhere['>='] = ['shop_distribution_access.create_time',$month];
            $monthWhere['<'] = ['shop_distribution_access.create_time',$today];
            $monthInfo = $model->do_select($monthWhere);
            if ($monthInfo['status'] == 200){
                $data['month'] = $monthInfo['data'][0]['total'];
                if ($monthInfo['data'][0]['num'] == 0){
                    $data['month'] = 0;
                }
            } else {
                return $monthInfo;
            }

            //不包含当天,总收益情况
            $where['<'] = ['shop_distribution_access.create_time',$today];
            $totalInfo = $model->do_select($where);
            if ($totalInfo['status'] == 200){
                $data['total'] = $totalInfo['data'][0]['total'];
                if ($totalInfo['data'][0]['num'] == 0){
                    $data['total'] = 0;
                }
            } else {
                return $totalInfo;
            }

            //我的团队、我的客户、订单数量
            $userWhere = [];
            $userWhere['`key`'] = yii::$app->session['key'];
            $userWhere['merchant_id'] = yii::$app->session['merchant_id'];
            $userWhere['fields'] = "count(id) as num";
            $userWhere['level >= 1'] = null;
            $userWhere["parent_url like '%/{$userId}/%'"] = null;
            $myTeam = $userModel->findall($userWhere);
            if ($myTeam['status'] == 200){
                $data['my_team'] = $myTeam['data'][0]['num'];
            }else{
                return $myTeam;
            }
            unset($userWhere['level >= 1']);
            $myCustomer = $userModel->findall($userWhere);
            if ($myCustomer['status'] == 200){
                $data['my_customer'] = $myCustomer['data'][0]['num'];
            }else{
                return $myCustomer;
            }
            $orderModel = new OrderModel();
            $sql = "SELECT count( sda.order_sn ) AS num FROM shop_order_group sog RIGHT JOIN shop_distribution_access sda ON sda.order_sn = sog.order_sn WHERE sda.uid = {$userId} AND sda.KEY = '{$userWhere['`key`']}' AND sda.merchant_id = {$userWhere['merchant_id']} AND ( sda.type = 1 OR sda.type = 3 ) AND (sog.STATUS = 1 OR sog.STATUS = 3 OR sog.STATUS = 5 OR sog.STATUS = 6 OR sog.STATUS = 7)";
            $orderInfo = $orderModel->querySql($sql);

            if (count($orderInfo) > 0){
                $data['my_order'] = $orderInfo[0]['num'];
            }else{
                $data['my_order'] = 0;
                return $orderInfo;
            }

            return result(200, "请求成功",$data);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOrder(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['status'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new DistributionAccessModel();
            $where['shop_distribution_access.key'] = yii::$app->session['key'];
            $where['shop_distribution_access.merchant_id'] = yii::$app->session['merchant_id'];
            $where['shop_distribution_access.uid'] = yii::$app->session['user_id'];
            $where['or'] = ['or',['=','shop_distribution_access.type', 1],['=','shop_distribution_access.type', 3]];
            if ($params['status'] == 1){
                $where['or'] = ['or',['=', 'shop_order_group.status', 6],['=', 'shop_order_group.status', 7]];
            }else{
                $where['or'] = ['or',['=','shop_order_group.status', 1],['=','shop_order_group.status', 3],['=','shop_order_group.status', 5]];
            }
            if (isset($params['searchName'])){
                $where['shop_user.nickname'] = ['like', "{$params['searchName']}"];
            }
            $where['field'] = "shop_order_group.order_sn,shop_order_group.payment_money,shop_order_group.create_time,shop_user.nickname,shop_user.avatar,shop_distribution_access.money as distribution_money";
            $where['join'][] = ['left join', 'shop_order_group', 'shop_distribution_access.order_sn = shop_order_group.order_sn'];
            $where['join'][] = ['left join', 'shop_user', 'shop_order_group.user_id = shop_user.id'];
            $goodsWhere = $where;
            if (isset($params['limit'])){
                $where['limit'] = $params['limit'];
            }
            if (isset($params['page'])){
                $where['page'] = $params['page'];
            }
            $array = $model->do_select($where);

            $goodsWhere['field'] = "shop_order.*";
            $goodsWhere['join'][] = ['right join', 'shop_order', 'shop_distribution_access.order_sn = shop_order.order_group_sn'];
            $goodsWhere['limit'] = false;
            $goodsInfo = $model->do_select($goodsWhere);

            if ($array['status'] == 200 && $goodsInfo['status'] == 200){
                foreach ($array['data'] as $k=>$v){
                    foreach ($goodsInfo['data'] as $key=>$val){
                        if ($v['order_sn'] == $val['order_group_sn']){
                            $array['data'][$k]['goods'][] = $val;
                        }
                    }
                }
            }
            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUser(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            //设置类目 参数
            $must = ['type'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $userModel = new UserModel();
            $userId = yii::$app->session['user_id'];
            $userWhere['`key`'] = yii::$app->session['key'];
            $userWhere['merchant_id'] = yii::$app->session['merchant_id'];
            $userWhere['fields'] = "id,nickname,avatar,money,level,level_id,commission,create_time,update_time";
            if (isset($params['searchName'])){
                $userWhere["nickname like '%{$params['searchName']}%'"] = null;
            }
            if ($params['type'] == 1){ //1=我的团队,其他查询客户
                $userWhere['level >= 1'] = null;
            }
            if (!isset($params['status']) || $params['status'] != 1){  //客户中已产生佣金的
                if (isset($params['limit'])){
                    $userWhere['limit'] = $params['limit'];
                }else{
                    $userWhere['limit'] = 10;
                }
                if (isset($params['page'])){
                    $userWhere['page'] = $params['page'];
                }else{
                    $userWhere['page'] = 1;
                }
            }
            $userWhere["parent_url like '%/{$userId}/%'"] = null;
            $userInfo = $userModel->findall($userWhere);
            if ($userInfo['status'] != 200){
                return result(500, "未查询到信息");
            }

            //各下单订单数量
            $model = new DistributionAccessModel();
            $where['shop_distribution_access.key'] = yii::$app->session['key'];
            $where['shop_distribution_access.merchant_id'] = yii::$app->session['merchant_id'];
            $where['shop_distribution_access.uid'] = $userId;
            $where['shop_distribution_access.type'] = 1;
            if (isset($params['searchName'])){
                $where['shop_user.nickname'] = ['like', "{$params['searchName']}"];
            }
            $where['or'] = ['or',['=','shop_order_group.status', 6],['=','shop_order_group.status', 7]];
            $where['field'] = "sum(shop_order_group.payment_money) as pay_money,count(shop_order_group.user_id) as num,shop_order_group.user_id";
            $where['join'][] = ['left join', 'shop_order_group', 'shop_distribution_access.order_sn = shop_order_group.order_sn'];
            $where['join'][] = ['left join', 'shop_user', 'shop_order_group.user_id = shop_user.id'];
            $where['groupBy'] = 'shop_order_group.user_id';
            $where['limit'] = false;
            $array = $model->do_select($where);

            //统计各会员的小等级名称
            $agentModel = new AgentModel();
            $agentWhere['key'] = $params['key'];
            $agentWhere['merchant_id'] = yii::$app->session['uid'];
            $agentWhere['status'] = 1;
            $agentWhere['limit'] = false;
            $agentInfo = $agentModel->do_select($agentWhere);  //可用的代理商等级
            $operatorModel = new OperatorModel();
            $operatorWhere['key'] = $params['key'];
            $operatorWhere['merchant_id'] = yii::$app->session['uid'];
            $operatorWhere['status'] = 1;
            $operatorWhere['limit'] = false;
            $operatorInfo = $operatorModel->do_select($operatorWhere);  //可用的运营商等级

            $count = 0;
            foreach ($userInfo['data'] as $k=>$v){
                switch ($v['level']) {
                    //普通会员
                    case "0":
                        $userInfo['data'][$k]['level_name'] = '普通会员';
                        break;
                    //超级会员
                    case "1":
                        $userInfo['data'][$k]['level_name'] = '超级会员';
                        break;
                    //代理商
                    case "2":
                        foreach ($agentInfo['data'] as $key=>$val){
                            if ($val['id'] == $v['level_id']){
                                $userInfo['data'][$k]['level_name'] = $val['name'];
                            }
                        }
                        break;
                    //运营商
                    case "3":
                        foreach ($operatorInfo['data'] as $key=>$val){
                            if ($val['id'] == $v['level_id']){
                                $userInfo['data'][$k]['level_name'] = $val['name'];
                            }
                        }
                        break;
                    default:
                        break;
                }
                $userInfo['data'][$k]['num'] = 0;
                $userInfo['data'][$k]['pay_money'] = 0;
                if (isset($array['data'])){
                    foreach ($array['data'] as $key=>$val){
                        if ($val['user_id'] == $v['id']){
                            $userInfo['data'][$k]['num'] = $val['num'];
                            $userInfo['data'][$k]['pay_money'] = $val['pay_money'];
                        }
                    }
                }
                if ($userInfo['data'][$k]['pay_money'] != 0 && isset($params['status']) && $params['status'] == 1){
                    $user[] = $userInfo['data'][$k];
                    $count = $count + 1;
                }
            }

            if (isset($user)){
                $userInfo['data'] = $user;
                $userInfo['count'] = $count;
            }

            return $userInfo;
        } else {
            return result(500, "请求方式错误");
        }
    }

}