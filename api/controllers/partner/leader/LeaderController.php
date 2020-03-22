<?php

namespace app\controllers\partner\leader;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\partnerUser\PartnerUserModel;
use app\models\tuan\UserModel;
use yii;
use app\models\system\SystemAreaModel;

class LeaderController extends yii\web\PartnerController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置


    /**
     * 合伙人列表
     * @return array
     * @throws yii\db\Exception
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $data['merchant_id'] = yii::$app->session['m_id'];
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
                $query->andWhere(['or', ['like', 'realname', $params['searchName']], ['=', 'phone', $params['searchName']], ['like', 'nickname', $params['searchName']]]);
            }
            $query->andWhere(['shop_tuan_leader.delete_time' => null]);
            $query->andWhere(['shop_tuan_leader.partner_id' => yii::$app->session['partner_id']]);
            if (isset($params['time'])) {
                $time = explode("to", $params['time']);
                $start_time = strtotime(trim($time[0] . " 00:00:00"));
                $end_time = strtotime(trim($time[1] . " 23:59:59"));
                $query->andWhere(['>=', 'shop_tuan_leader.create_time', $start_time]);
                $query->andWhere(['<=', 'shop_tuan_leader.create_time', $end_time]);
            }
            if (isset($params['is_self']) && !empty($params['is_self'])) {
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
            //if (isset($params['key'])) {
                $query->andWhere(['shop_tuan_leader.key' => yii::$app->session['key']]);
            //}
            if (isset($params['city'])) {
                for ($i = 0; $i < count($area['data']); $i++) {
                    $whereArea = ['city_code' => $area['data'][$i]['code']];
                }
                $query->andWhere(['or', $whereArea]);
            }
            if (isset($params['addr'])) {
                if ($params['addr'] == 1) {
                    $query->andWhere(['<>', 'addr', '']);
                } else {
                    $query->andWhere(['=', 'addr', '']);
                }
            }
            $query->limit($limit)->offset($offset);
            $res = $query->all();
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
            $user = $userModel->findall(['`key`' => yii::$app->session['key'], 'merchant_id' => yii::$app->session['merchant_id']]);
            //检测是否开启合伙人设置
            $app = new AppAccessModel();
            $info = $app->find(['key' => yii::$app->session['key'], 'open_partner' => 1]);
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
                            $where['shop_tuan_user.`key`'] = yii::$app->session['key'];
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

}
