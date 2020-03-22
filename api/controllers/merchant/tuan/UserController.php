<?php

namespace app\controllers\merchant\tuan;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\partnerUser\PartnerUserModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\tuan\UserModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\tuan\LeaderModel;
use app\models\system\SystemAreaModel;

class UserController extends MerchantController
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
            //    $model = new LeaderModel();
            $data['merchant_id'] = yii::$app->session['uid'];

            if (isset($params['city'])) {
                $areaModel = new SystemAreaModel();

                $area = $areaModel->do_select(['name' => ['like', "{$params['city']}"]]);
                if ($area['status'] != 200) {
                    return $area;
                }
            }

            $query = (new \yii\db\Query())->from("shop_tuan_leader");
            if (isset($params['limit'])) {
                $limit = $params['limit'];
                unset($params['limit']);
            } else {
                $limit = 10;
            }
            if (isset($params['page'])) {
                if ($params['page'] - 1 < 0) {
                    $offset = 0;
                } else {
                    $offset = ($params['page'] - 1) * $limit;
                }
                unset($params['page']);
            } else {
                $offset = 0;
            }
            $sql = "(select count(id) from shop_order_group where shop_order_group.leader_self_uid = shop_tuan_leader.id) as self_number,"
                . "(select count(id) from shop_order_group where shop_order_group.leader_uid = shop_tuan_leader.id) as number, "
                . "(select sum(money) from shop_user_balance where shop_user_balance.uid = shop_tuan_leader.uid and status =1 and shop_user_balance.type != 8 and shop_user_balance.type != 7) as sum_money, "
                . "(select sum(money) from shop_user_balance where shop_user_balance.uid = shop_tuan_leader.uid and status =0) as on_money,";

            $query = $query->select("shop_tuan_leader.*,shop_user.phone,shop_user.nickname,shop_user.balance as user_balance," . $sql)->leftJoin('shop_user', 'shop_tuan_leader.uid = shop_user.id')->orderBy('shop_tuan_leader.id desc')->limit($limit)->offset($offset);


            if (isset($params['searchName'])) {
//                $query->orWhere(['like', 'realname', $params['searchName']]);
//                $query->orWhere(['phone' => $params['searchName']]);
//                $query->orWhere(['like', 'nickname', $params['searchName']]);
                $query->andWhere(['or', ['like', 'realname', $params['searchName']], ['=', 'shop_user.phone', $params['searchName']], ['like', 'nickname', $params['searchName']]]);
            }
            $query->andWhere(['shop_tuan_leader.delete_time' => null]);
            if (isset($params['time'])) {
                $time = explode("to", $params['time']);
                $start_time = strtotime(trim($time[0] . " 00:00:00"));
                $end_time = strtotime(trim($time[1] . " 23:59:59"));
                $query->andWhere(['>=', 'shop_tuan_leader.create_time', $start_time]);
                $query->andWhere(['<=', 'shop_tuan_leader.create_time', $end_time]);
            }
            if (isset($params['is_self'])) {
                $query->andWhere(['shop_tuan_leader.is_self' => $params['is_self']]);
            }
            if (isset($params['type'])) {

                if ($params['type'] == 1) {
                    $query->andWhere(['shop_tuan_leader.status' => 1]);
                }
                if ($params['type'] == 3) {
                    $query->andWhere(['or', ['=', 'shop_tuan_leader.status', 0], ['=', 'shop_tuan_leader.status', 2]]);
                }
                if ($params['type'] == 2) {
                    $query->andWhere(['shop_tuan_leader.status' => 2]);
                }
                if ($params['type'] == 0) {
                    $query->andWhere(['shop_tuan_leader.status' => 0]);
                }
            }
            if (isset($params['audit_time'])) {
                $time = explode("to", $params['audit_time']);
                $start_time = strtotime(trim($time[0] . " 00:00:00"));
                $end_time = strtotime(trim($time[1] . " 23:59:59"));
                $query->andWhere(['>=', 'check_time', $start_time]);
                $query->andWhere(['<=', 'check_time', $end_time]);
            }
            if (isset($params['key'])) {
                $query->andWhere(['shop_tuan_leader.key' => $params['key']]);
            }
            if (isset($params['city'])) {
                for ($i = 0; $i < count($area['data']); $i++) {
                    $whereArea = ['city_code' => $area['data'][$i]['code']];
                }
                $query->andWhere(['or', $whereArea]);
            }

            if (isset($params['addr'])) {
                if ($params['addr'] == 1) {
//                    $whereArea = ['addr' => ""];
//                    $query->andWhere(['or', $whereArea]);//原写法，判断写反了
                    $query->andWhere(['<>', 'addr', '']);
                } else {
                    $query->andWhere(['=', 'addr', '']);
                }
            }
            $query->andWhere(['<>', 'shop_tuan_leader.uid', 0]);
            if (isset($params['warehouse_id'])) {
               // $whereWarehouse_id = ['warehouse_id' =>$params['warehouse_id']];
                $query->andWhere(['or',['warehouse_id' => $params['warehouse_id']],['warehouse_id' =>$params['warehouse_id1']]]);
            }


            //$query->andWhere(['or', ['like', 'realname', $params['searchName']], ['=', 'phone', $params['searchName']], ['like', 'nickname', $params['searchName']]]);
            $query->limit($limit)->offset($offset);
            $res = $query->all();
           //  print_r($query->createCommand()->getRawSql());die();
////
            //   die();

            $count = $query->limit("")->all();

            if (!empty($res)) {
                $array['status'] = 200;
                $array['message'] = "请求成功";
                $array['data'] = $res;
                foreach ($array['data'] as $key => $value) {
                    empty($value['create_time']) ? false : $array['data'][$key]['format_create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                    empty($value['update_time']) ? false : $array['data'][$key]['format_update_time'] = date('Y-m-d H:i:s', $value['update_time']);
                }
            } else {
                $array['status'] = 500;
                $array['message'] = "查询失败";
            }

            $userModel = new \app\models\shop\UserModel;
            $user = $userModel->findall(['`key`' => $params['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
            //检测是否开启合伙人设置
            $app = new AppAccessModel();
            $info = $app->find(['key' => $params['key'], 'open_partner' => 1]);
            $panrtner = 0;
            if($info['status'] == 200){
                $panrtner = 1;
            }
            if ($user['status'] == 200 && $array['status'] == 200) {
                for ($i = 0; $i < count($array['data']); $i++) {
                    //查询合伙人信息
                    $array['data'][$i]['partner_name'] = '';
                    if($panrtner){
                        $partnerModel = new PartnerUserModel();
                        $partnerInfo = $partnerModel->one(['id' => $array['data'][$i]['partner_id']]);
                        if($partnerInfo['status'] == 200){
                            $array['data'][$i]['partner_name'] = $partnerInfo['data']['name'];
                        }
                    }
                    $areaModel = new SystemAreaModel();
                    $province = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['province_code']]);
                    $city = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['city_code']]);
                    $area = $areaModel->do_column(['field' => 'name', 'code' => $array['data'][$i]['area_code']]);
                    $array['data'][$i]['province'] = $province['data'][0];
                    $array['data'][$i]['city'] = $city['data'][0];
                    $array['data'][$i]['area'] = $area['data'][0];

                    $sql = "select sum(payment_money)as num  from shop_order_group where (status = 6 or status = 7) and user_id = {$array['data'][$i]['uid']}  and delete_time is null";
                    $res = $userModel->querySql($sql);
                    $array['data'][$i]['money'] = $res[0]['num'];
                    $array['data'][$i]['audit_name'] = "";
                    if ($array['data'][$i]['admin_uid'] != 0) {
                        $mUserModel = new \app\models\merchant\user\MerchantModel();
                        $mUser = $mUserModel->find(['id' => $array['data'][$i]['area']['admin_uid']]);
                        $array['data'][$i]['audit_name'] = $mUser['data']['name'];
                    }
                    if ($array['data'][$i]['admin_sub_uid'] != 0) {
                        $mUserModel = new \app\models\merchant\system\UserModel();
                        $mUser = $mUserModel->find(['id' => $array['data'][$i]['area']['admin_sub_uid']]);
                        $array['data'][$i]['audit_name'] = $mUser['data']['name'];
                    }
                    if ($array['data'][$i]['recommend_uid'] != 0) {
                        $mUserModel = new \app\models\shop\UserModel();
                        $mUser = $mUserModel->find(['id' => $array['data'][$i]['area']['recommend_uid']]);
                        $array['data'][$i]['recommend_name'] = $mUser['data']['name'];
                    }
                    for ($j = 0; $j < count($user['data']); $j++) {
                        if ($array['data'][$i]['uid'] == $user['data'][$j]['id']) {
                            $array['data'][$i]['phone'] = $user['data'][$j]['phone'];
                            $array['data'][$i]['nickname'] = $user['data'][$j]['nickname'];
                            $array['data'][$i]['avatar'] = $user['data'][$j]['avatar'];

                            $where['leader_uid'] = $user['data'][$j]['id'];
                            $where['shop_tuan_user.`key`'] = $params['key'];
                            $where['shop_tuan_user.status'] = 1;
                            $tuanUserModel = new UserModel();
                            $data['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_tuan_user.uid'];

                            $rs = $tuanUserModel->do_select($where);
                            if ($rs['status'] === 200) {
                                $array['data'][$i]['count'] = $rs['count'];
                            } else {
                                //要么错误，要么未查询到团员信息
                                $array['data'][$i]['count'] = 0;
                            }
                        }
                    }
                }
            } else {
                return result(204, "未查到数据");
            }
            $array['count'] = count($count);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new LeaderModel();
            $params['id'] = $id;
            $array = $model->do_one($params);
            if ($array['status'] == 200) {
                $userModel = new \app\models\shop\UserModel;
                $user = $userModel->findall(['`key`' => $params['key'], 'merchant_id' => yii::$app->session['merchant_id']]);

                $areaModel = new SystemAreaModel();
                $province = $areaModel->do_column(['field' => 'name', 'code' => $array['data']['province_code']]);
                $city = $areaModel->do_column(['field' => 'name', 'code' => $array['data']['city_code']]);
                $area = $areaModel->do_column(['field' => 'name', 'code' => $array['data']['area_code']]);
                $array['data']['province'] = $province['data'][0];
                $array['data']['city'] = $city['data'][0];
                $array['data']['area'] = $area['data'][0];

                $sql = "select sum(payment_money)as num  from shop_order_group where status = 6 or status = 7 and user_id = {$array['data']['uid']} ";
                $res = $userModel->querySql($sql);
                $array['data']['money'] = $res[0]['num'];
                $array['data']['audit_name'] = "";
                if ($array['data']['admin_uid'] != 0) {
                    $mUserModel = new \app\models\merchant\user\MerchantModel();
                    $mUser = $mUserModel->find(['id' => $array['data']['area']['admin_uid']]);
                    $array['data']['audit_name'] = $mUser['data']['name'];
                }
                if ($array['data']['admin_sub_uid'] != 0) {
                    $mUserModel = new \app\models\merchant\system\UserModel();
                    $mUser = $mUserModel->find(['id' => $array['data']['area']['admin_sub_uid']]);
                    $array['data']['audit_name'] = $mUser['data']['name'];
                }
                if ($array['data']['recommend_uid'] != 0) {
                    $mUserModel = new \app\models\shop\UserModel();
                    $mUser = $mUserModel->find(['id' => $array['data']['area']['recommend_uid']]);
                    $array['data']['recommend_name'] = $mUser['data']['name'];
                }

                for ($j = 0; $j < count($user['data']); $j++) {
                    if ($array['data']['uid'] == $user['data'][$j]['id']) {
                        $array['data']['phone'] = $user['data'][$j]['phone'];
                        $array['data']['nickname'] = $user['data'][$j]['nickname'];
                        $array['data']['avatar'] = $user['data'][$j]['avatar'];

                        if ($user['data'][$j]['sex'] == 0) {
                            $array['data']['sex'] = "保密";
                        } else if ($user['data'][$j]['sex'] == 1) {
                            $array['data']['sex'] = "男";
                        } else if ($user['data'][$j]['sex'] == 2) {
                            $array['data']['sex'] = "女";
                        }
                    }
                }

                $balanceModel = new \app\models\shop\BalanceModel();
                $balance = $balanceModel->do_one(['uid' => $array['data']['uid'], 'status' => 1, 'is_send' => 1]);
                $array['data']['ali'] = "";
                $array['data']['pay_name'] = "";
                $array['data']['pay_realname'] = "";
                $array['data']['pay_number'] = "";
                if ($balance['status'] == 200) {
                    if ($balance['data']['send_type'] == 2) {
                        $array['data']['ali'] = $balance['data']['pay_number'];
                    }
                    if ($balance['data']['send_type'] == 3) {
                        $array['data']['pay_realname'] = $balance['data']['realname'];
                        $array['data']['pay_number'] = $balance['data']['pay_number'];
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionAdd() {
//        if (yii::$app->request->isPost) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new UserModel();
//            $params['merchant_id'] = yii::$app->session['uid'];
//            $array = $model->do_add($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    public function actionUpdate($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new LeaderModel();
//
            $where['id'] = $id;
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = $params['key'];
            //校验应用是否设置合伙人
            $app = new AppAccessModel();
            $params['partner_id'] = 0;
            $info = $app->find(['`key`' => $params['key'], 'open_partner' => 1]);
            if($info['status'] == 200 && isset($params['longitude'])){
                //查询合伙人id
                $partnerModel = new PartnerUserModel();
                $result = $partnerModel->getAddrGD($params['longitude'] . ',' . $params['latitude'], 1);
                $partnerInfo = $partnerModel->one(['adcode'=>$result]);
                if($partnerInfo['status'] == 200){
                    $params['partner_id'] = $partnerInfo['data']['id'];
                }
            }
            unset($params['key']);
            $array = $model->do_update($where, $params);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $where['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '团长';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;
//            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 团长审核
     * @param $id
     * @return array
     */
    public function actionAudit($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new LeaderModel();
            $where['id'] = $id;
            $where['merchant_id'] = yii::$app->session['uid'];
            $where['key'] = $params['key'];
            if ($params['status'] == 1) {
                $data['status'] = 1;
                $data['check_time'] = time();
            } else {
                $data['status'] = 2;
            }
            $tr = Yii::$app->db->beginTransaction();
            $array = $model->do_update($where, $data);
            if ($array['status'] == 200) {
                $user_model = new \app\models\shop\UserModel();
                $where_par['id'] = $id;
                $leader_info = $model->do_one($where_par);
                if ($leader_info['status'] != 200) {
                    $tr->rollBack();
                    return result(500, "缺少数据");
                }
                $up_data['is_leader'] = 1;
                $up_data['id'] = $leader_info['data']['uid'];
                $up_data['`key`'] = $leader_info['data']['key'];
                $up_res = $user_model->update($up_data);

                $leaderUserModel = new UserModel();
                $leaderUserModel->do_delete(['uid' => $leader_info['data']['uid']]);
                $data['merchant_id'] = yii::$app->session['uid'];
                $data['key'] = $params['key'];
                $data['uid'] = $leader_info['data']['uid'];
                $data['leader_uid'] = $leader_info['data']['uid'];
                $data['status'] = 1;
                unset($data['check_time']);
                $leaderUserModel->do_add($data);
                if ($up_res['status'] == 200) {
                    $shopUserModel = new \app\models\shop\UserModel();
                    $shopUser = $shopUserModel->find(['id' => $leader_info['data']['uid']]);

                    $tempModel = new \app\models\system\SystemMiniTemplateModel();
                    $minitemp = $tempModel->do_one(['id' => 29]);

                    $tempParams = array(
                        'keyword1' => $leader_info['data']['realname'],
                        'keyword2' => $leader_info['data']['format_create_time'],
                        'keyword3' => $leader_info['data']['area_name'],
                        'keyword4' => $shopUser['data']['nickname'],
                    );

                    $tempAccess = new SystemMerchantMiniAccessModel();
                    $taData = array(
                        'key' => $leader_info['data']['key'],
                        'merchant_id' => $leader_info['data']['merchant_id'],
                        'mini_open_id' => $shopUser['data']['mini_open_id'],
                        'template_id' => 33,
                        'number' => '0',
                        'template_params' => json_encode($tempParams),
                        'template_purpose' => 'order',
                        'page' => "pages/group/creategroup/creategroup/{$leader_info['data']['uid']}",
                        'status' => '-1',
                    );
                    $tempAccess->do_add($taData);

                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $data['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordData['module_name'] = '团长审核';
                    $operationRecordModel->do_add($operationRecordData);

                    $tr->commit();
                    return result(200, "更新成功");
                }
            }
            $tr->rollBack();
            return result(500, "更新失败");
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id)
    {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $model = new UserModel();
            $data['leader_uid'] = $id;
            $data['key'] = $params['key'];
            $data['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_delete($data);
            if ($array['status'] == 200){
                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $data['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '删除';
                $operationRecordData['operation_id'] = $id;
                $operationRecordData['module_name'] = '团长';
                $operationRecordModel->do_add($operationRecordData);
            }
            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionLeaguememberlist($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $data['shop_tuan_user.leader_uid'] = $id;
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $data['shop_user.nickname'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            if (isset($params['page'])) {
                $data['page'] = $params['page'];
            }
            if (isset($params['limit'])) {
                $data['limit'] = $params['limit'];
            }
            $data['shop_tuan_user.`key`'] = $params['key'];
            $data['shop_tuan_user.status'] = 1;

            if (isset($params['type'])) {
                $data['shop_tuan_user.is_verify'] = $params['type'];
            }
            $tuanUserModel = new UserModel();
            $data['join'][] = ['inner join', 'shop_user', 'shop_user.id = shop_tuan_user.uid'];

            $array = $tuanUserModel->do_select($data);
            if ($array['status'] == 200) {
                return $array;
            } else {
                return result(204, "未查询到数据");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionLeaguememberupdata()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $where['uid'] = $params['id'];
            $tuanUserModel = new UserModel();
            $data['is_verify'] = $params['is_verify'];
            if (!empty($where['uid'])) {
                $array = $tuanUserModel->do_update($where, $data);

                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = json_encode($params['id']);
                    $operationRecordData['module_name'] = '团长';
                    $operationRecordModel->do_add($operationRecordData);
                }
                return $array;
            } else {
                return result(500, "未选择团员");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    //团长绑定商品
    public function actionGoods($id)
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取地址栏参数
            $where['id'] = $id;
            $data = array();
            for ($i = 0; $i < count($params['goods_ids']); $i++) {
                if ($i == 0) {
                    $data['goods_ids'] = $params['goods_ids'][$i];
                } else {
                    $data['goods_ids'] = $data['goods_ids'] . "," . $params['goods_ids'][$i];
                }
            }
            $tuanUserModel = new LeaderModel();
            if (!empty($where['id'])) {
                $array = $tuanUserModel->do_update($where, $data);
                return $array;
            } else {
                return result(500, "未选择团长");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionGoodsList($id)
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $tuanUserModel = new LeaderModel();
            $where['id'] = $id;
            $array = $tuanUserModel->do_one($where);
            if ($array['status'] == 200) {
                $array['data']['goods_ids'] = explode(',', $array['data']['goods_ids']);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
