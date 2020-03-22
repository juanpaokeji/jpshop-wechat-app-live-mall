<?php

namespace app\controllers\tuan;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\distribution\AgentModel;
use app\models\merchant\distribution\OperatorModel;
use app\models\merchant\partnerUser\PartnerUserModel;
use app\models\shop\GroupOrderModel;
use app\models\shop\ShopUserModel;
use app\models\shop\TuanLeaderModel;
use yii;
use yii\db\Exception;
use yii\web\ShopController;
use app\models\tuan\LeaderModel;
use app\models\system\SystemSmsAccessModel;
use app\models\tuan\ConfigModel;
use app\models\system\SystemAreaModel;
use app\models\tuan\UserModel;

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class UserController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置
    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['key'] = yii::$app->session['key'];
            $params['latitude'] = (float)$params['latitude'];
            $params['longitude'] = (float)$params['longitude'];

            if (isset($params['name'])) {
                if (empty($params['name'])) {
                    $where = '';
                } else {
                    $where = '  `area_name` like "%' . $params['name'] . '%" and ';
                }
            } else {
                $where = '';
            }


            $configModel = new ConfigModel();
            $res = $configModel->do_one(['merchant_id' => $params['merchant_id'], 'key' => yii::$app->session['key']]);

            if ($res['status'] == 204) {
                return result(500, '商户未配置团购信息！');
            }
            if ($res['status'] == 500) {
                return result(500, '请求失败！');
            }
            // $res['data']['leader_range'] = 100000000000; //测试使用
            //校验商户是否关闭合伙人设置
            $app = new \app\models\merchant\app\AppAccessModel();
            $info = $app->find(['key' => Yii::$app->session['key'], 'open_partner' => 1]);
            $partner = 0;
            if($info['status'] == 200){
                $partner = 1;
            }
            $sql = "SELECT "
                . " *, ("
                . " 6371 * acos("
                . " cos(radians({$params['latitude']})) * cos(radians(latitude)) * cos("
                . " radians(longitude) - radians({$params['longitude']})"
                . " ) + sin(radians({$params['latitude']})) * sin(radians(latitude))"
                . " )"
                . " ) AS juli "
                . " FROM"
                . " shop_tuan_leader"
                . " HAVING"
                . " juli < {$res['data']['leader_range']} and `key` = '{$params['key']}' and merchant_id = {$params['merchant_id']} and state = 0  and status =1";
            if($partner && isset($params['partner_id']) && !empty($params['partner_id'])){
                $sql .= " and partner_id = {$params['partner_id']}";
            }
            $sql .= " order by juli asc ";
            $res = yii::$app->db->createCommand($sql)->queryAll();
            if (empty($res)) {
                return result(204, '查询失败');
            } else {
                foreach ($res as $k => $v) {
                    $res[$k]['avatar'] = ShopUserModel::instance()->get_value2(['id' => $v['uid']], 'avatar') ?? '';
                    $res[$k]['juli'] = round($v['juli'], 3);
                }

                return ['status' => 200, 'message' => '请求成功', 'data' => $res];
            }
            return $res;
        } else {
            return result(500, "请求方式错误");
        }
    }

//

    public function actionTuan()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new LeaderModel();
            $data['uid'] = yii::$app->session['user_id'];
            $array = $model->one($data);

            if ($array['status'] == 204) {
                $status['status'] = -1;
            }
            if ($array['status'] == 200) {
                $status['status'] = (int)$array['data']['status'];
                if ($array['data']['status'] == 0) {
                    $message = "您已经在申请中，请等待审核";
                }
                if ($array['data']['status'] == 1) {
                    $message = "您已经是团长";
                }
                if ($array['data']['status'] == 1) {
                    $message = "您申请的团长已失败，请重新填写资料提交审核";
                }
            }
            return result(200, $message, $status);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参


            $configModel = new ConfigModel();
            $config = $configModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'key' => yii::$app->session['key']]);

            if ($config['status'] != 200) {
                return result(500, '请求失败');
            }

            $model = new LeaderModel();
            $params['user_id'] = yii::$app->session['user_id'];
            $res = $model->do_one(['uid' => yii::$app->session['user_id'], 'key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
            $bool = false;
            if ($res['status'] == 204) {
                $bool = true;
            }
            if (isset($res['data'])) {
                if ($res['data']['status'] == 2) {
                    $bool = true;
                }
            }

            if ($bool == true) {
                $userModel = new \app\models\shop\UserModel;
                $user = $userModel->find(['id' => $params['user_id'], '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);

                if ($user['status'] != 200) {
                    return result(500, "请求失败");
                }
//                if ($user['data']['phone'] == "") {
//                    if (!isset($params['phone']) && !isset($params['vercode'])) {
//                        return result(500, "请填写手机号和验证码");
//                    } else {
//                        $smsmodel = new SystemSmsAccessModel();
//                        $data['phone'] = $params['phone'];
//                        $rs = $smsmodel->find($data['phone']);
//
//                        if ($rs['status'] != 200) {
//                            return result(500, "未查询到验证码!");
//                        }
//                        if ($rs['data']['code'] != $params['vercode']) {
//                            return result(500, "验证码不正确!");
//                        }
//                        unset($params['vercode']);
//                    }
//                } else {
//                    if ($user['data']['phone'] != $params['phone']) {
//                        return result(500, "您填写的手机号与原手机号不相同");
//                    }
//                }
                $must = array();
                if (isset($params['is_self'])) {
//                    if ($params['is_self'] == true || $params['is_self'] == 'true') {
//                        $params['is_self'] = 1;
//                    } else {
//                        $params['is_self'] = 0;
//                    }
                    if ($params['is_self'] == 1) {
                        $must = ['area_name', 'province_code', 'city_code', 'area_code', 'addr', 'longitude', 'latitude', 'realname'];
//                        $url = "https://restapi.amap.com/v3/geocode/regeo?output=json&location={$params['longitude']},{$params['latitude']}&key=bc55956766e813d3deb1f95e45e97d73&poitype=&radius=1000&extensions=all&batch=true&roadlevel=0";
//
//                        $address = json_decode(curlGet($url), true);
//
//                        if ($address['status'] == 0) {
//                            return result(500, $address['info']);
//                        }
//
//                        $systemArea = new SystemAreaModel();
//                        $province = $systemArea->do_one(['name' => $address['regeocodes'][0]['addressComponent']['province']]);
//                        $params['province_code'] = $province['data']['code'];
//                        $city = $systemArea->do_one(['name' => $address['regeocodes'][0]['addressComponent']['city']]);
//                        $params['city_code'] = $city['data']['code'];
//                        $area = $systemArea->do_one(['name' => $address['regeocodes'][0]['addressComponent']['district']]);
//                        $params['area_code'] = $area['data']['code'];
                    } else {
                        $must = ['province_code', 'city_code', 'area_code', 'realname'];
                    }
                } else {
                    $must = ['province_code', 'city_code', 'area_code', 'realname'];
                    $params['is_self'] = 0;
                }

                //设置类目 参数

                $rs = $this->checkInput($must, $params);
                if ($rs != false) {
                    return $rs;
                }
                $params['merchant_id'] = yii::$app->session['merchant_id'];
                $params['key'] = yii::$app->session['key'];
                $params['uid'] = yii::$app->session['user_id'];


                try {
                    $model->begin();
                    $userModel->update(['id' => yii::$app->session['user_id'], 'phone' => $params['phone'], '`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
                    unset($params['phone']);
                    unset($params['user_id']);
                    unset($params['vercode']);
                    $params['status'] = 0;
                    $params['tuan_express_fee'] = $config['data']['tuan_express_fee'];
                    if (isset($params['recommend_uid'])) {
                        if ($params['recommend_uid'] == "") {
                            unset($params['recommend_uid']);
                        }
                    }
                    //校验应用是否设置合伙人
                    $app = new AppAccessModel();
                    $params['partner_id'] = 0;
                    $info = $app->find(['key' => $params['key'], 'open_partner' => 1]);
                    if($info['status'] == 200){
                        //查询合伙人id
                        $partnerModel = new PartnerUserModel();
                        $result = $partnerModel->getAddrGD($params['longitude'] . ',' . $params['latitude'], 1);
                        $partnerInfo = $partnerModel->one(['adcode'=>$result]);
                        if($partnerInfo['status'] == 200){
                            $params['partner_id'] = $partnerInfo['data']['id'];
                        }
                    }
                    $array = $model->do_add($params);
                    $model->commit();
                } catch (Exception $ex) {
                    $model->rollback();
                    return result(500, '请求失败！');
                }
                return $array;
            } else if ($res['status'] == 200 && $res['data']['status'] == 0) {
                return result(500, '您已经申请,请等待审核！');
            } else if ($res['status'] == 200 && $res['data']['status'] == 1) {
                return result(500, '您已经是团长');
            } else {
                return result(500, "请求失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    //绑定团长，团员通过链接进来绑定团长
    public function actionLeader()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new UserModel();

            $array = $model->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'uid' => yii::$app->session['user_id']]);
            if ($array['status'] == 200) {
                return result(200, '请求成功');
            }
            if ($array['status'] == 500) {
                return result(500, '请求失败');
            }
            $leaderModel = new LeaderModel();
            $array = $leaderModel->do_one(['merchant_id' => yii::$app->session['merchant_id'], 'uid' => $params['id']]);
            if ($array['status'] != 200) {
                return result(500, '请求失败,该用户不是团长，没法绑定');
            }
            $data['leader_uid'] = $params['id'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['key'] = yii::$app->session['key'];
            $data['uid'] = yii::$app->session['user_id'];
            $data['status'] = 1;
            $array = $model->do_add($data);
            return $array;
//            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionToday()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $orderModel = new \app\models\shop\OrderModel();
            $sql = "select count(*)as number  from shop_order_group where is_tuan =1 and leader_uid = " . yii::$app->session['user_id'] . " and create_time <=" . time() . "";
            $res = $orderModel->querySql($sql);
            $array['data']['orderCount'] = $res[0]['number'];

            $sql = "select count(*)as number  from shop_order_group where is_tuan =1 and leader_uid = " . yii::$app->session['user_id'] . " and status = 1 and create_time <=" . time() . "";
            $res = $orderModel->querySql($sql);
            $array['data']['orderValid'] = $res[0]['number'];

            $sql = "select count(*)as number  from shop_order_group where is_tuan =1 and leader_uid = " . yii::$app->session['user_id'] . " and status = 1 group by user_id and create_time <=" . time() . "";
            $res = $orderModel->querySql($sql);
            $array['data']['orderUser'] = count($res) == 0 ? 0 : $res[0]['number'];

            $sql = "select sum(money)as number  from shop_user_balance where uid = " . yii::$app->session['user_id'] . " and type = 1 and create_time <=" . time() . "";
            $res = $orderModel->querySql($sql);
            $array['data']['orderBalance'] = count($res) == 0 ? 0 : $res[0]['number'];

            $sql = "select * from shop_user where id = " . yii::$app->session['user_id'] . "";
            $res = $orderModel->querySql($sql);
            $array['userMoney'] = $res[0]['balance'];
            $array['status'] = 200;
            $array['message'] = "请求成功";
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOrder()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $orderModel = new \app\models\shop\OrderModel();
            if ($params['type'] == 1) {
                $where['leader_self_uid'] = yii::$app->session['user_id'];
                $where['status'] = 2;
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                $where['tuan_status'] = 0;
                $where['`key`'] = yii::$app->session['key'];
                $where['is_tuan'] = 1;
                $array = $orderModel->findList($where);
            } else if ($params['type'] == 2) {
                $where['leader_self_uid'] = yii::$app->session['user_id'];
                $where['status'] = 2;
                $where['tuan_status'] = 1;
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                $where['`key`'] = yii::$app->session['key'];
                $where['is_tuan'] = 1;
                $array = $orderModel->findList($where);
            } else if ($params['type'] == 3) {
                $where['leader_self_uid'] = yii::$app->session['user_id'];
                $where['status'] = 2;
                $where['tuan_status'] = 2;
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                $where['`key`'] = yii::$app->session['key'];
                $where['is_tuan'] = 1;
                $array = $orderModel->findList($where);
            } else if ($params['type'] == 4) {
                $where['leader_self_uid'] = yii::$app->session['user_id'];
                $where['status'] = 6;
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                $where['`key`'] = yii::$app->session['key'];
                $where['is_tuan'] = 1;
                $array = $orderModel->findList($where);
            } else if ($params['type'] == 5) {
//                $where['leader_uid'] = yii::$app->session['user_id'];
//                $where['status'] = 6;
//                $where['merchant_id'] = yii::$app->session['merchant_id'];
//                $where['key'] = yii::$app->session['key'];
//                $where['is_tuan'] = 1;
//                $array = $orderModel->findAll($where);
                $array['status'] = 500;
                $array['message'] = "请求失败";
            } else if ($params['type'] == 6) {
                $userModel = new \app\models\tuan\UserModel();
                $where['field'] = "shop_tuan_user.*,shop_user.avatar as  user_avatar,shop_user.nickname as user_nickname ";
                $where['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_tuan_user.uid'];
                $where['shop_tuan_user.merchant_id'] = yii::$app->session['merchant_id'];
                $where['leader_uid'] = yii::$app->session['user_id'];
                $where['shop_tuan_user.key'] = yii::$app->session['key'];
                $where['shop_tuan_user.status'] = 1;
                $where['limit'] = false;
                $array = $userModel->do_select($where);
            } else if ($params['type'] == 7) {
                $userModel = new LeaderModel();
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                $where['recommend_uid'] = yii::$app->session['user_id'];
                $where['key'] = yii::$app->session['key'];
                $where['status'] = 1;
                $where['limit'] = false;
                $array = $userModel->do_select($where);
            } else if ($params['type'] == 8) {
                $where['leader_uid'] = yii::$app->session['user_id'];
                $where['status'] = 2;
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                //   $where['tuan_status'] = 0;
                $where['`key`'] = yii::$app->session['key'];
                $where['is_tuan'] = 1;
                $array = $orderModel->findList($where);
            } else if ($params['type'] == 9) {
                $where['leader_uid'] = yii::$app->session['user_id'];
                $where['status'] = 1;
                $where['merchant_id'] = yii::$app->session['merchant_id'];
                $where['tuan_status'] = 0;
                $where['`key`'] = yii::$app->session['key'];
                $where['is_tuan'] = 1;
                $array = $orderModel->findList($where);
            } else {
                $array['status'] = 500;
                $array['message'] = "请求失败";
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionTotal()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $orderModel = new \app\models\shop\OrderModel();

            $sql = "select count(id) as number from  shop_order_group where DATE_SUB(CURDATE(), INTERVAL 7 DAY) <= date(FROM_UNIXTIME(create_time)) and leader_uid = " . yii::$app->session['user_id'] . " and status =2 and is_tuan = 1  and merchant_id=" . yii::$app->session['merchant_id'] . " and`key` = '" . yii::$app->session['key'] . "'";
            $res = $orderModel->querySql($sql);
            $array['data']['week'] = count($res) == 0 ? 0 : $res[0]['number'];

            $sql = "select count(id) as number  from  shop_order_group where DATE_SUB(CURDATE(), INTERVAL 1 MONTH) <= date(FROM_UNIXTIME(create_time)) and leader_uid = " . yii::$app->session['user_id'] . " and status = 2  and is_tuan = 1 and merchant_id=" . yii::$app->session['merchant_id'] . " and `key`= '" . yii::$app->session['key'] . "'";
            $res = $orderModel->querySql($sql);
            $array['data']['month'] = count($res) == 0 ? 0 : $res[0]['number'];

            $sql = "select count(id) as number  from  shop_order_group where  leader_uid = " . yii::$app->session['user_id'] . " and status = 2  and is_tuan = 1 and merchant_id=" . yii::$app->session['merchant_id'] . "  and `key` = '" . yii::$app->session['key'] . "'";
            $res = $orderModel->querySql($sql);
            $array['data']['count'] = count($res) == 0 ? 0 : $res[0]['number'];

            $sql = "select count(id) as number  from  shop_order_group where  leader_uid = " . yii::$app->session['user_id'] . " and is_tuan = 1 and merchant_id=" . yii::$app->session['merchant_id'] . " and `key` = '" . yii::$app->session['key'] . "'";
            $res = $orderModel->querySql($sql);
            $array['data']['order'] = count($res) == 0 ? 0 : $res[0]['number'];

            $sql = "select sum(money) as number  from shop_user_balance where  uid = " . yii::$app->session['user_id'] . " and type = 1 ";
            $res = $orderModel->querySql($sql);
            $array['data']['balance'] = count($res) == 0 ? 0 : $res[0]['number'];

            $sql = "select sum(number)as number from shop_order where order_group_sn in (select * from (select order_sn from shop_order_group where is_tuan = 1 and leader_uid = " . yii::$app->session['user_id'] . ")as t)";
            $res = $orderModel->querySql($sql);
            $array['data']['number'] = $res[0]['number'] == null ? 0 : $res[0]['number'];

            $sql = "select count(id) as number  from shop_tuan_user where  leader_uid = " . yii::$app->session['user_id'] . " and status= 1";
            $res = $orderModel->querySql($sql);
            $array['data']['user'] = count($res) == 0 ? 0 : $res[0]['number'];

            $sql = "select count(*) as number  from shop_tuan_leader where  recommend_uid = " . yii::$app->session['user_id'] . " and status= 1";
            $res = $orderModel->querySql($sql);
            $array['data']['leader'] = count($res) == 0 ? 0 : $res[0]['number'];

            $array['status'] = 200;
            $array['message'] = "请求成功";
            return $array;
        }
    }

    /**
     * 团长订单统计（按商品）
     * @author wmy
     * @return array
     */
    public function actionOrderStatistics()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (!isset($params['time'])) {
                $params['time'] = 1;
            }
            if (!isset($params['page'])) {
                $params['page'] = 1;
            }
            if (!isset($params['limit'])) {
                $params['limit'] = 10;
            }
            $time = (int)$params['time'];
            switch ($time) {
                case 1: // 今日
                    $start_time = strtotime(date('Y-m-d', time())); // 今日12点
                    break;
                case 2: // 近七天
                    $start_time = strtotime(date("Y-m-d", strtotime("-7 day")));; // 7天12点
                    break;
                case 3: // 近三十天
                    $start_time = strtotime(date("Y-m-d", strtotime("-30 day"))); // 30天12点
                    break;
                case 4:
                    $start_time = 1262278800; // 获取2010-01-01 01:00:00
                    break;
                default:
                    $start_time = strtotime(date('Y-m-d', time())); // 今日12点
            }
            $end_time = time(); // 当前时间
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $leader_uid = yii::$app->session['user_id'];
            $leader_self_uid = yii::$app->session['user_id'];
            $orderModel = new \app\models\shop\OrderModel();
            $where = "sg.`key` = '{$key}' 
            AND sg.merchant_id = {$merchant_id} 
            AND (sg.leader_self_uid = {$leader_uid} OR sg.leader_uid = {$leader_self_uid})
            AND sg.is_tuan = 1 
            AND sg.`status` in (1,3,5,6,7)
            AND sg.create_time >= {$start_time} AND sg.create_time <= {$end_time}";
            // 查询 总订单数和支付总金额
            $total_sql = "SELECT SUM(payment_money) as total_price,COUNT(id) as total_number from shop_order_group as sg WHERE " . $where;
            $total_res = $orderModel->querySql($total_sql);
            $array['total_price'] = $total_res[0]['total_price'] == null ? 0 : $total_res[0]['total_price'];
            $array['total_number'] = $total_res[0]['total_number'];
            // 购买的商品总条数
            $where .= " GROUP BY so.`name`";
            $goods_limit_sql = "SELECT so.`name` as total FROM shop_order_group AS sg
                          LEFT JOIN shop_order AS so ON sg.order_sn = so.order_group_sn WHERE " . $where;
            $goods_limit_res = $orderModel->querySql($goods_limit_sql);
            // 购买的商品分页
            $startRow = ($params['page'] - 1) * $params['limit'];
            $where .= " ORDER BY numbers DESC  LIMIT {$startRow},{$params['limit']}";
            $goods_sql = "SELECT so.`name` as goods_name,SUM(so.number) as numbers FROM shop_order_group AS sg
                          LEFT JOIN shop_order AS so ON sg.order_sn = so.order_group_sn WHERE " . $where;
            $goods_res = $orderModel->querySql($goods_sql);
            if ($goods_res) {
                foreach ($goods_res as $key => $val) {
                    $goods_res[$key]['text'] = $val['goods_name'] == null ? '' : $val['goods_name'];
                    $goods_res[$key]['numbers'] = $val['numbers'] == null ? 0 : $val['numbers'];
                }
            }
            $array['list'] = $goods_res;
            $array['total'] = count($goods_limit_res);
            $array['start_time'] = date("Y-m-d H:i:s", $start_time);
            $array['end_time'] = date("Y-m-d  H:i:s", $end_time);
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 团长订单统计（按用户）
     * @author wmy
     * @return array
     */
    public function actionOrderStatisticsUser()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            if (!isset($params['time'])) {
                $params['time'] = 1;
            }
            if (!isset($params['page'])) {
                $params['page'] = 1;
            }
            if (!isset($params['limit'])) {
                $params['limit'] = 10;
            }
            $time = (int)$params['time'];
            switch ($time) {
                case 1: // 今日
                    $start_time = strtotime(date('Y-m-d', time())); // 今日12点
                    break;
                case 2: // 近七天
                    $start_time = strtotime(date("Y-m-d", strtotime("-7 day")));; // 7天12点
                    break;
                case 3: // 近三十天
                    $start_time = strtotime(date("Y-m-d", strtotime("-30 day"))); // 30天12点
                    break;
                case 4:
                    $start_time = 1262278800; // 获取2010-01-01 01:00:00
                    break;
                default:
                    $start_time = strtotime(date('Y-m-d', time())); // 今日12点
            }
            $end_time = time(); // 当前时间
            $key = yii::$app->session['key'];
            $merchant_id = yii::$app->session['merchant_id'];
            $leader_uid = yii::$app->session['user_id'];
            $leader_self_uid = yii::$app->session['user_id'];
            $orderModel = new \app\models\shop\OrderModel();
            $where = "sg.`key` = '{$key}' 
            AND sg.merchant_id = {$merchant_id} 
            AND (sg.leader_self_uid = {$leader_uid} OR sg.leader_uid = {$leader_self_uid})
            AND sg.is_tuan = 1 
            AND sg.`status` in (1,3,5,6,7)
            AND sg.create_time >= {$start_time} AND sg.create_time <= {$end_time}";
            // 查询 总订单数和支付总金额
            $total_sql = "SELECT SUM(payment_money) as total_price,COUNT(id) as total_number from shop_order_group as sg WHERE " . $where;
            $total_res = $orderModel->querySql($total_sql);
            $array['total_price'] = $total_res[0]['total_price'] == null ? 0 : $total_res[0]['total_price'];
            $array['total_number'] = $total_res[0]['total_number'];
            // 用户下单总条数
            $where .= " GROUP BY su.`nickname`";
            $user_limit_sql = "SELECT su.`nickname` as nickname FROM shop_order_group AS sg
                          LEFT JOIN shop_user AS su ON sg.user_id = su.id WHERE " . $where;
            $user_limit_res = $orderModel->querySql($user_limit_sql);
            //用户下单分页
            $startRow = ($params['page'] - 1) * $params['limit'];
            $where .= " ORDER BY numbers DESC  LIMIT {$startRow},{$params['limit']}";
            $user_sql = "SELECT su.`nickname` as nickname,count(sg.id) as numbers FROM shop_order_group AS sg
                          LEFT JOIN shop_user AS su ON sg.user_id = su.id WHERE " . $where;
            $user_res = $orderModel->querySql($user_sql);
            if ($user_res) {
                foreach ($user_res as $key => $val) {
                    $user_res[$key]['text'] = $val['nickname'] == null ? '' : $val['nickname'];
                    $user_res[$key]['numbers'] = $val['numbers'] == null ? 0 : $val['numbers'];
                }
            }
            $array['list'] = $user_res;
            $array['total'] = count($user_limit_res);
            $array['start_time'] = date("Y-m-d H:i:s", $start_time);
            $array['end_time'] = date("Y-m-d  H:i:s", $end_time);
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionLevel()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $data['user_id'] = yii::$app->session['user_id'];
            $data['merchant_id'] = yii::$app->session['merchant_id'];
            $data['key'] = yii::$app->session['key'];
            $userModel = new \app\models\shop\UserModel();
            $user = $userModel->find(['id' => $data['user_id']]);
            if ($user['status'] != 200) {
                return $user;
            }

            $levelModel = new \app\models\merchant\user\LevelModel();
            $level = $levelModel->do_one(['id' => $user['data']['leader_level'], 'key' => $data['key'], 'merchant_id' => $data['merchant_id']]);

            $levels = $levelModel->do_select(['key' => $data['key'], 'merchant_id' => $data['merchant_id'], 'orderby' => 'min_exp asc']);
            if ($levels['status'] == 204) {
                $array = array(
                    'leader' => 0,
                    'leader_exp' => 0,
                    'level' => 0,
                    'next_level' => 0,
                    'next_level_name' => 0,
                    'info' => 0
                );
            } else {
                $lv = 0;
                $next_level = 0;
                $reward_ratio = 0;

                if ($level['status'] == 200) {
                    $lv = $level['data']['name'];
                    $reward_ratio = $level['data']['reward_ratio'];
                    for ($i = 0; $i < count($levels['data']); $i++) {
                        if ($levels['data'][$i]['id'] == $level['data']['id']) {
                            $leader_exp = $levels['data'][$i]['min_exp'];
                            if (count($levels['data']) == $i) {
                                $next_level = $levels['data'][$i]['min_exp'];
                                $next_level_name = $levels['data'][$i]['name'];
                            } else {
                                $next_level = $levels['data'][$i + 1]['min_exp'];
                                $next_level_name = $levels['data'][$i + 1]['name'];
                            }
                        }
                    }
                    // $next_level = $level['data']['name'];
                    $array = array(
                        'leader' => $user['data']['leader_exp'],
                        'level' => $lv,
                        'leader_exp' => $leader_exp,
                        'next_level' => $next_level,
                        'next_level_name' => $next_level_name,
                        'info' => $reward_ratio
                    );
                } else {
                    $array = array(
                        'leader' => 0,
                        'leader_exp' => 0,
                        'level' => 0,
                        'next_level' => $levels['data'][0]['min_exp'],
                        'next_level_name' => $levels['data'][0]['name'],
                        'info' => 0
                    );
                }
            }


            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    //查询上次订单团长
    public function actionLast()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
//            $must = ['latitude', 'longitude'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return json_encode($rs, JSON_UNESCAPED_UNICODE);
//            }
//
//            $params['latitude'] = (float) $params['latitude'];
//            $params['longitude'] = (float) $params['longitude'];
//            $data['user_id'] = yii::$app->session['user_id'];
//            $data['merchant_id'] = yii::$app->session['merchant_id'];
//            $data['`key`'] = yii::$app->session['key'];
//            $data['leader_self_uid!=0'] = null;
//            $orderModel = new \app\models\shop\OrderModel();
//            $order = $orderModel->select($data);
//
//            if ($order['status'] != 200) {
//                return $order;
//            }
//
//            $configModel = new ConfigModel();
//            $res = $configModel->do_one(['merchant_id' => $data['merchant_id'], 'key' => yii::$app->session['key']]);
//
//            if ($res['status'] == 204) {
//                return result(500, '商户未配置团购信息！');
//            }
//            if ($res['status'] == 500) {
//                return result(500, '请求失败！');
//            }
//            //  $res['data']['leader_range'] = 100000000000; //测试使用
//            $sql = "select * from (SELECT*, ROUND(6378.138 * 2 * ASIN(	SQRT(POW(SIN(({$params['latitude']} * PI() / 180 - latitude * PI() / 180) / 2),2) + COS({$params['longitude']} * PI() / 180) * COS(latitude * PI() / 180) * POW(" .
//                    "SIN(({$params['longitude']} * PI() / 180 - longitude * PI() / 180) / 2),2))) * 1000) AS juli FROM shop_tuan_leader where  merchant_id = {$data['merchant_id']} and `key` = '{$data['`key`']}' and status = 1 and is_self = 1 and uid = {$order['data']['leader_self_uid']}) " . "stl where juli <={$res['data']['leader_range']}   ORDER BY juli desc";
//
////            $sql = "select * from shop_tuan_leader ";
//            $res = yii::$app->db->createCommand($sql)->queryAll();

            //if (count($res) > 0) {

            $userModel = new \app\models\shop\UserModel();
           // $user = $userModel->find(['id' => yii::$app->session['user_id']]);

            $orderModel = new GroupOrderModel();
            $order = $orderModel->one(['user_id'=> yii::$app->session['user_id']]);

            $leaderModel = new LeaderModel();
            $leader = $leaderModel->do_one(['key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'uid' => $order['data']['leader_self_uid']]);

            //var_dump($leader);die();


            $data = $userModel->find(['id' => $order['data']['leader_self_uid']]);
            if ($leader['status'] == 200) {
                $leader['data']['avatar'] = $data['data']['avatar'];
            }
            return $leader;
//            } else {
//                return result(204, "查询失败");
//            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSupplier(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new LeaderModel();
            $data['supplier_id'] = $params['supplier_id'];
            $array = $model->do_one($data);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOne($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
           $userModel = new \app\models\shop\UserModel();
//            $user = $userModel->find(['id' => $id]);

            $leaderModel = new LeaderModel();
            $leader = $leaderModel->do_one(['key' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id'], 'uid' =>$id]);
            $data = $userModel->find(['id' => $id]);
            if ($leader['status'] == 200) {
                $leader['data']['avatar'] = $data['data']['avatar'];
            }
            return $leader;

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new \app\models\shop\UserModel();
            $userId = yii::$app->session['user_id'];
            //当前登录用户信息
            $userInfo = $model->find(['id'=>$userId]);
            if ($userInfo['status'] != 200){
                return $userInfo;
            }
            if (!empty($userInfo['data']['parent_id']) && $userInfo['data']['leader_uid'] != 0){
                return result(500, "该用户已被推荐过");
            }
            //推荐人信息
            $parentInfo = $model->find(['id'=>$params['id']]);
            if ($parentInfo['status'] != 200){
                return $parentInfo;
            }
            //查询当前被推荐用户以前是否推荐过别人
            $userWhere['`key`'] = yii::$app->session['key'];
            $userWhere['merchant_id'] = yii::$app->session['merchant_id'];
            $userWhere['parent_id'] = $userId;
            $res = $model->findall($userWhere);
            //不能推荐自己，超级会员以上才能进行推广,当前被推荐人以前未被推荐过,当前被推荐人以前未推荐过别人
            if ($params['id'] != $userId && $parentInfo['data']['level'] >= 1 && empty($userInfo['data']['parent_id']) && $res['status'] == 204){
                //上三级父节点url
                $data['parent_url'] = '/' . $params['id'] . '/';
                if (!empty($parentInfo['data']['parent_url'])){
                    $parentUrl = explode('/',trim($parentInfo['data']['parent_url'],'/'));
                    $data['parent_url'] .= $parentUrl[0] . '/';
                    if (isset($parentUrl[1])){
                        $data['parent_url'] .= $parentUrl[1] . '/';
                    }
                    if (isset($parentUrl[2])){
                        $data['parent_url'] .= $parentUrl[2] . '/';
                    }
                }
                $data['parent_id'] = $params['id'];
            }

            $data['id'] = $userId;
            $data['`key`'] = yii::$app->session['key'];
            $data['leader_uid'] = $id;

            $array = $model->update($data);

            $appAccessModel = new AppAccessModel();
            $appInfo = $appAccessModel->find(['key'=>$userId]);
            //不能推荐自己，超级会员以上才能进行推广,当前登陆用户以前未被推荐过,当前登陆用户以前未推荐过别人
            if ($params['id'] != $userId && $array['status'] == 200 && $parentInfo['data']['level'] >= 1 && empty($userInfo['data']['parent_id']) && $res['status'] == 204){
                //推荐完，查询父级是否可以升级，并修改信息,判断是否开启手动升级审核
                $parentLev = $this->getLevel($params['id'],1);
                if ($parentLev['up_level'] > $parentLev['level'] || ($parentLev['up_level'] == $parentLev['level'] && $parentLev['up_level_id'] != $parentLev['level_id'])){ //需要升级的等级比实际等级高
                    $parentData['id'] = $params['id'];
                    $parentData['`key`'] = yii::$app->session['key'];
                    $parentData['up_level'] = $parentLev['up_level'];
                    $parentData['up_level_id'] = $parentLev['up_level_id'];
                    $parentData['fan_number'] = $parentLev['fan_number'];
                    if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0){
                        $parentData['level'] = $parentLev['up_level'];
                        $parentData['level_id'] = $parentLev['up_level_id'];
                    }else{
                        $parentData['is_check'] = 0;
                    }
                    $model->update($parentData);
                }
                if (!empty($parentInfo['data']['parent_url'])){
                    //推荐完，查询祖父级是否可以升级，并修改信息,判断是否开启手动升级审核
                    $grandFatherLev = $this->getLevel($parentUrl[0],2);
                    if ($grandFatherLev['up_level'] > $grandFatherLev['level'] || ($grandFatherLev['up_level'] == $grandFatherLev['level'] && $grandFatherLev['up_level_id'] != $grandFatherLev['level_id'])){ //需要升级的等级比实际等级高
                        $grandFatherData['id'] = $parentUrl[0];
                        $grandFatherData['`key`'] = yii::$app->session['key'];
                        $grandFatherData['secondhand_fan_number'] = $grandFatherLev['secondhand_fan_number'];
                        $grandFatherData['up_level'] = $grandFatherLev['up_level'];
                        $grandFatherData['up_level_id'] = $grandFatherLev['up_level_id'];
                        if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0){
                            $grandFatherData['level'] = $grandFatherLev['up_level'];
                            $grandFatherData['level_id'] = $grandFatherLev['up_level_id'];

                        }else{
                            $grandFatherData['is_check'] = 0;
                        }
                        $model->update($grandFatherData);
                    }
                    if (isset($parentUrl[1])){
                        //推荐完，查询曾祖父级是否可以升级，并修改信息,判断是否开启手动升级审核
                        $ggFatherLev = $this->getLevel($parentUrl[1],2);
                        if ($ggFatherLev['up_level'] > $ggFatherLev['level'] || ($ggFatherLev['up_level'] == $ggFatherLev['level'] && $ggFatherLev['up_level_id'] != $ggFatherLev['level_id'])) { //需要升级的等级比实际等级高
                            $ggFatherData['id'] = $parentUrl[1];
                            $ggFatherData['`key`'] = yii::$app->session['key'];
                            $ggFatherData['up_level'] = $ggFatherLev['up_level'];
                            $ggFatherData['up_level_id'] = $ggFatherLev['up_level_id'];
                            $ggFatherData['secondhand_fan_number'] = $ggFatherLev['secondhand_fan_number'];
                            if ($appInfo['status'] == 200 && $appInfo['data']['distribution_is_open'] == 0){
                                $ggFatherData['level'] = $ggFatherLev['up_level'];
                                $ggFatherData['level_id'] = $ggFatherLev['up_level_id'];
                            }else{
                                $ggFatherData['is_check'] = 0;
                            }
                            $model->update($ggFatherData);
                        }
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //获取目前符合的等级 type = 1为父级 type = 2为祖父、曾祖父级
    public function getLevel($id,$type = 1){
        $model = new \app\models\shop\UserModel();
        $userInfo = $model->find(['id'=>$id]);

        if ($type == 1){
            $data['fan_number'] = $userInfo['data']['fan_number'] + 1;
            $data['secondhand_fan_number'] = $userInfo['data']['secondhand_fan_number'];
        }else{
            $data['fan_number'] = $userInfo['data']['fan_number'];
            $data['secondhand_fan_number'] = $userInfo['data']['secondhand_fan_number'] + 1;
        }

        $sql = "SELECT sum(sog.payment_money) as total FROM `shop_user` su RIGHT JOIN `shop_order_group` sog ON sog.user_id = su.id WHERE su.parent_id = {$id} AND sog.status = 6 OR sog.status = 7";
        $total = $model->querySql($sql);

        $operatorModel = new OperatorModel();
        $operatorWhere['key'] = yii::$app->session['key'];
        $operatorWhere['merchant_id'] = yii::$app->session['merchant_id'];
        $operatorWhere['status'] = 1;
        $operatorWhere['limit'] = false;
        $operatorInfo = $operatorModel->do_select($operatorWhere);
        if (isset($operatorInfo['data'])){
            foreach ($operatorInfo['data'] as $k=>$v){
                if ($v['fan_number_buy'] <= $total[0]['total'] && $v['fan_number'] <= $data['fan_number'] && $v['secondhand_fan_number'] <= $data['secondhand_fan_number']){
                    $data['level'] = $userInfo['data']['level'];
                    $data['level_id'] = $userInfo['data']['level_id'];
                    $data['up_level'] = 3;
                    $data['up_level_id'] = $v['id'];
                    return $data;
                }
            }
        }

        $agentModel = new AgentModel();
        $agentWhere['key'] = yii::$app->session['key'];
        $agentWhere['merchant_id'] = yii::$app->session['merchant_id'];
        $agentWhere['status'] = 1;
        $agentWhere['limit'] = false;
        $agentInfo = $agentModel->do_select($agentWhere);
        if (isset($agentInfo['data'])){
            foreach ($agentInfo['data'] as $k=>$v){
                if ($v['fan_number_buy'] <= $total[0]['total'] && $v['fan_number'] <= $data['fan_number'] && $v['secondhand_fan_number'] <= $data['secondhand_fan_number']){
                    $data['level'] = $userInfo['data']['level'];
                    $data['level_id'] = $userInfo['data']['level_id'];
                    $data['up_level'] = 2;
                    $data['up_level_id'] = $v['id'];
                    return $data;
                }
            }
        }

        $data['level'] = $userInfo['data']['level'];
        $data['level_id'] = $userInfo['data']['level_id'];
        $data['up_level'] = 1;
        $data['up_level_id'] = 0;
        return $data;
    }




}
