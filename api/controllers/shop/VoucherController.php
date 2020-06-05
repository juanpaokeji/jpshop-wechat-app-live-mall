<?php

namespace app\controllers\shop;

use app\models\shop\LuckyVoucherModel;
use app\models\shop\OrderModel;
use app\models\shop\UserModel;
use yii;
use yii\web\ShopController;
use yii\base\Exception;
use app\models\shop\VoucherModel;
use app\models\shop\VoucherTypeModel;
use app\controllers\common\CommonController;

class VoucherController extends ShopController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\ShopFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['vouchertype'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new VoucherModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];

            $params['status'] = 1;
            $array = $model->findall($params);
            if($array['status']==200){
                for($i=0;$i<count($array['data']);$i++){
                    $array['data'][$i]['price'] = floatval($array['data'][$i]['price']);
                    $array['data'][$i]['full_price'] = floatval($array['data'][$i]['full_price']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionVoucher(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $model = new VoucherModel();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $params['supplier_id'] = $params['supplier_id'];
            if (isset($params['goods_id'])){
                $goodsKey = explode(",",$params['goods_id']);
                foreach ($goodsKey as $k=>$v){
                    $goodsKey[$k] = "goods_id = {$v} ";
                }
                $goodsKey[] = "goods_id = 0 ";
                $keyStr = implode(" or ", $goodsKey);
                $keyStr = '('.$keyStr.')';
                $params[$keyStr] = null;
                unset($params['goods_id']);
            }
            $params['status'] = 1;
            $array = $model->findall($params);
            if($array['status']==200){
                for($i=0;$i<count($array['data']);$i++){
                    $array['data'][$i]['price'] = floatval($array['data'][$i]['price']);
                    $array['data'][$i]['full_price'] = floatval($array['data'][$i]['full_price']);
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //新人红包
    public function actionNew()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $typeModel = new VoucherTypeModel();
            $time = time();
            $sql = "select * from shop_voucher_type  where to_date >={$time} and delete_time is null and status =1 and merchant_id = {$params['merchant_id']} and `key` = '{$params['`key`']}' and type = 2";

            $data = $typeModel->querySql($sql);

            if (count($data) > 0) {
                $model = new VoucherModel();
                $params['type_id'] = $data[0]['id'];
                $res = $model->findall($params);

                if ($res['status'] == 200) {
                    return result(500, '新人红包无法领取');
                } else {
                    $orderModel = new \app\models\shop\OrderModel();
                    $order = $orderModel->findAll(['user_id' => $params['user_id']]);
                    if ($order['status'] == 200) {
                        return result(500, '新人红包无法领取');
                    } else if ($order['status'] == 500) {
                        return result(500, '系统错误');
                    } else {
                        return result(200, '可以领取新人红包');
                    }
                }
            } else {
                return result(500, '新人红包无法领取');
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $voucher = new VoucherModel();
            $must = ['type_id'];
            $cc = new CommonController();
            $params['cdkey'] = $cc->generateCode();
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            //获取优惠券类型
            $type = new VoucherTypeModel();
            $typedata['id'] = $params['type_id'];
            $voutype = $type->find($typedata);
            if ($voutype['status'] == 204) {
                $array = ['status' => 400, 'message' => '该type_id不存在',];
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            if ($voutype['data']['count'] <= $voutype['data']['send_count']) {
                return result(500, "该优惠券已到达上限！");
            }
            //新人红包只能新人领取
            if ($voutype['data']['type'] == 2){
                $orderModel = new OrderModel();
                $sql = "select count(id)as num from shop_order_group where (status >2 or status =1) and  user_id = {$params['user_id']}";
                $is_recruits = $orderModel->querySql($sql);
                if ($is_recruits[0]['num'] != 0) {
                    return result(500, "您不是新用户，无法领取新用户专用优惠券！");
                }
            }

//            if ((int)$voutype['data']['from_date1'] > time()) {
//                return result(500, "该优惠券活动未开始");
//            }
            if ((int)$voutype['data']['to_date1'] < time()) {
                return result(500, "该优惠券活动已结束");
            }


            if ($voutype['data']['receive_count'] != 0) {
                $data['`key`'] = yii::$app->session['key'];
                $data['merchant_id'] = yii::$app->session['merchant_id'];
                $data['user_id'] = yii::$app->session['user_id'];
                $data['type_id'] = $params['type_id'];
                $res = $voucher->findall($data);
                if ($res['status'] == 200) {
                    if ($res['count'] >= $voutype['data']['receive_count']) {
                        return result(500, "该优惠券已领取！");
                    }
                }
            }


            //优惠券新增参数
            $vdata['cdkey'] = $params['cdkey'];
            $vdata['type_id'] = $params['type_id'];
            $vdata['type_name'] = $voutype['data']['name'];
            $vdata['goods_id'] = $voutype['data']['goods_id'];
            $vdata['category_id'] = $voutype['data']['category_id'];
            $vdata['status'] = 1;
            $vdata['start_time'] = time();
            $vdata['end_time'] = ($voutype['data']['days'] * 24 * 60 * 60) + ($vdata['start_time']);
            $vdata['is_exchange'] = 0;
            $vdata['merchant_id'] = $params['merchant_id'];
            try {
                $vdata['`key`'] = $params['`key`'];
                $vdata['is_used'] = 0;
                $vdata['price'] = $voutype['data']['price'];
                $vdata['full_price'] = $voutype['data']['full_price'];
                $vdata['user_id'] = $params['user_id'];
                //开始事务
                $transaction = Yii::$app->db->beginTransaction();
                $array = $voucher->add($vdata);
                //更新优惠券个数
                $typeparams['send_count'] = $voutype['data']['send_count'] + 1;
                $typeparams['id'] = $params['type_id'];
                $type->update($typeparams);
                $transaction->commit(); //只有执行了commit(),对于上面数据库的操作才会真正执行
            } catch (Exception $e) {
                $transaction->rollBack(); //回滚
                return result(500, "添加失败");
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionVouchertype()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
            $voucher = new VoucherTypeModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['status'] = 1;
            $params['collection_type'] = 1;
            $params['(type!=3 and type!=9)'] = null;
            $time = time();
            $params["from_date<={$time}"] = null;
            $params["to_date>={$time}"] = null;
            $array = $voucher->finds($params);
            if($array['status']==200){
                for($i=0;$i<count($array['data']);$i++){
                    $array['data'][$i]['price'] = floatval($array['data'][$i]['price']);
                    $array['data'][$i]['full_price'] = floatval($array['data'][$i]['full_price']);
                }
            }
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionReceive()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $typeModel = new VoucherTypeModel();
            $time = time();
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $params['user_id'] = yii::$app->session['user_id'];
            $sql = "select * from shop_voucher_type  where to_date >={$time} and delete_time is null and status =1 and merchant_id = {$params['merchant_id']} and `key` = '{$params['`key`']}' and type = 2";
            $data = $typeModel->querySql($sql);
            if (count($data) > 0) {
                for ($i = 0; $i < count($data); $i++) {
                    $sql = "select id from shop_voucher where user_id = {$params['user_id']} ";
                    $vou = $typeModel->querySql($sql);
                    if (count($vou) == 0) {
                        $cc = new CommonController();
                        $params['cdkey'] = $cc->generateCode();
                        if ((int)$data[$i]['from_date'] < time() && (int)$data[$i]['to_date'] > time()) {
                            //优惠券新增参数
                            $vdata['cdkey'] = $params['cdkey'];
                            $vdata['type_id'] = $data[$i]['id'];
                            $vdata['type_name'] = $data[$i]['name'];
                            $vdata['status'] = 1;
                            $vdata['start_time'] = time();
                            $vdata['end_time'] = ($data[$i]['days'] * 24 * 60 * 60) + ($vdata['start_time']);
                            $vdata['is_exchange'] = 0;
                            $vdata['merchant_id'] = $params['merchant_id'];
                            $vdata['`key`'] = $params['`key`'];
                            $vdata['is_used'] = 0;
                            $vdata['price'] = $data[$i]['price'];
                            $vdata['full_price'] = $data[$i]['full_price'];
                            $vdata['user_id'] = $params['user_id'];
                            $voucherModel = new VoucherModel();
                            $array = $voucherModel->add($vdata);
                        }
                    }
                }
                return result(200, "领取成功");
            } else {
                return result(500, "领取失败");
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 运气红包分享
     * @return array
     */
    public function actionShare()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            //检测 当前订单是否支付
            $orderModel = new OrderModel();
            if (!isset($params['order_sn']) || empty($params['order_sn'])) {
                return result(500, "缺少参数");
            }
            $params['`key`'] = yii::$app->session['key'];
            $params['merchant_id'] = yii::$app->session['merchant_id'];
            $sql = "select * from shop_order_group  where  delete_time is null and `status` = 1 and merchant_id = {$params['merchant_id']} and `key` = '{$params['`key`']}' and transaction_order_sn = '{$params['order_sn']}'";
            $info = $orderModel->querySql($sql);
            if (empty($info)) {
                return result(204, "当前订单已不能发送运气红包");
            }
            //检测运气红包类型券是否有效
            $voucherTypeModel = new VoucherTypeModel();
            $typeWhere['`key`'] = yii::$app->session['key'];
            $typeWhere['merchant_id'] = $params['merchant_id'];
            $typeWhere['type'] = 3;
            $typeWhere['`status`'] = 1;
            $time = time();
            $typeWhere["from_date<={$time}"] = null;
            $typeWhere["to_date>={$time}"] = null;
            $typeInfo = $voucherTypeModel->finds($typeWhere);
            if ($typeInfo['status'] != 200) {
                return result(204, "运气红包数据错误");
            }
            //查询已领取量
            $voucherModel = new VoucherModel();
            $sql = "select count(id) as total from shop_voucher  where  delete_time is null and status =1 and merchant_id = {$typeWhere['merchant_id']} and `key` = '{$typeWhere['`key`']}' and order_sn = '{$params['order_sn']}'";
            $total = $voucherModel->querySql($sql);
            if ($typeInfo['data'][0]['count'] <= $total[0]['total']) {
                return result(500, "运气红包发放量已用完了");
            }
            if ($typeInfo['data'][0]['receive_count'] != 0) {
                if ($typeInfo['data'][0]['receive_count'] <= $total[0]['total']) {
                    return result(500, "运气红包大于领取次数");
                }
            }
            if ($typeInfo['data'][0]['send_count'] >= $typeInfo['data'][0]['count']) {
                return result(500, "运气红包发放量不能大于已发放数量");
            }
            //shop_lucky_voucher 表是否有记录没有创建
            $luckyModel = new LuckyVoucherModel();
            $luckWhere['key'] = yii::$app->session['key'];
            $luckWhere['merchant_id'] = yii::$app->session['merchant_id'];
            $luckWhere['order_sn'] = $params['order_sn'];
            $luckWhere['status'] = 1;
            $luckyInfo = $luckyModel->one($luckWhere);
            if ($luckyInfo['status'] != 200) {
                $luckWhere['lucky_number'] = rand(3, 10);
                $res = $luckyModel->add($luckWhere);
                if ($res['status'] == 200) {
                    return result(200, "分享成功","http://" . $_SERVER['HTTP_HOST'] . "/api/web/uploads/yqhb.png" );
                }
            }else{
                return result(200, "分享成功","http://" . $_SERVER['HTTP_HOST'] . "/api/web/uploads/yqhb.png");
            }
            return result(200, "分享失败");
        } else {
            return result(500, "请求方式错误");
        }
    }


    /**
     * 幸运红包领取
     * @return array
     * @throws yii\db\Exception
     */
    public function actionReceivePacket()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            if (!isset($params['order_sn']) || empty($params['order_sn'])) {
                return result(500, "缺少参数");
            }
            $typeWhere['`key`'] = yii::$app->session['key'];
            $typeWhere['merchant_id'] = yii::$app->session['merchant_id'];
            $user_id = yii::$app->session['user_id'];
            //当前领取的人是否是幸运数字
            $luckyModel = new LuckyVoucherModel();
            $info = $luckyModel->one(['order_sn' => $params['order_sn'], 'key' => $typeWhere['`key`'], 'merchant_id' => $typeWhere['merchant_id'], 'status' => 1]);
            if ($info['status'] != 200) {
                return result(500, "数据出错了");
            }
            //查询当前红包已领取个数
            $voucherModel = new VoucherModel();
            $sql = "select * from shop_voucher  where  delete_time is null and status =1 and merchant_id = {$typeWhere['merchant_id']} and `key` = '{$typeWhere['`key`']}' and order_sn = '{$params['order_sn']}'";
            $list = $voucherModel->querySql($sql);
            $total = count($list);
            $userModel = new UserModel();
            if ($total > 0) {
                foreach ($list as $key => &$val) {
                    $user_info = $userModel->find(['id' => $val['user_id']]);
                    $val['nickname'] = '';
                    $val['avatar'] = '';
                    if ($user_info['status'] == 200) {
                        $val['nickname'] = $user_info['data']['nickname'];
                        $val['avatar'] = $user_info['data']['avatar'];
                    }
                    $val['is_licky'] = 0;
                    if ($key + 1 == $info['data']['lucky_number']) {
                        $val['is_licky'] = 1;
                    }
                    $val['create_time'] = date('Y-m-d',$val['create_time']);
                }
            }
            $result['receive_list'] = $list;
            $result['receive_info'] = [];
            if ($total >= 10) {
                return result(500, "已领取10次无法再次领取",$result);
            }
            //检测当前用户是否领取过红包
            $sql = "select * from shop_voucher  where  delete_time is null and status =1 and merchant_id = {$typeWhere['merchant_id']} and `key` = '{$typeWhere['`key`']}' and order_sn = '{$params['order_sn']}' and user_id = '{$user_id}'";
            $voInfo = $voucherModel->querySql($sql);
            if (!empty($voInfo)) {
                $result['receive_info']['start_time'] = date('Y-m-d', $voInfo[0]['start_time']);
                $result['receive_info']['end_time'] = date('Y-m-d', $voInfo[0]['end_time']);
                $result['receive_info']['price'] = $voInfo[0]['price'];
                $result['receive_info']['pull_price'] = $voInfo[0]['full_price'];
                return result(201, "已领取过",$result);
            }
            //检测运气红包是否被禁用
            $voucherTypeModel = new VoucherTypeModel();
            $typeWhere['type'] = 3;
            $typeWhere['`status`'] = 1;
            $time = time();
            $typeWhere["from_date<={$time}"] = null;
            $typeWhere["to_date>={$time}"] = null;
            $typeInfo = $voucherTypeModel->finds($typeWhere);
            if ($typeInfo['status'] != 200) {
                return result(500, "运气红包数据错误", $result);
            }

            if ($typeInfo['data'][0]['count'] <= $total) {
                return result(500, "运气红包发放量已用完了", $result);
            }
            if ($typeInfo['data'][0]['receive_count'] != 0) {
                if ($typeInfo['data'][0]['receive_count'] <= $total) {
                    return result(500, "运气红包大于领取次数", $result);
                }
            }
            if ($typeInfo['data'][0]['send_count'] >= $typeInfo['data'][0]['count']) {
                return result(500, "运气红包发放量不能大于已发放数量", $result);
            }
            $num = $total + 1;
            if ($num == $info['data']['lucky_number']) {
                $price = bcadd($typeInfo['data'][0]['lucky_min_price'], bcmul(mt_rand() / mt_getrandmax(), ($typeInfo['data'][0]['lucky_price'] - $typeInfo['data'][0]['lucky_min_price']), 1), 1);
            } else {
                $price = bcadd($typeInfo['data'][0]['min_price'], bcmul(mt_rand() / mt_getrandmax(), ($typeInfo['data'][0]['price'] - $typeInfo['data'][0]['min_price']), 1), 1);
            }
            $cc = new CommonController();
            $params['cdkey'] = $cc->generateCode();
            //优惠券新增参数
            $vdata['cdkey'] = $params['cdkey'];
            $vdata['order_sn'] = $params['order_sn'];
            $vdata['type_id'] = $typeInfo['data'][0]['id'];
            $vdata['type_name'] = $typeInfo['data'][0]['name'];
            $vdata['status'] = 1;
            $vdata['start_time'] = $typeInfo['data'][0]['from_date'];
            $vdata['end_time'] = ($typeInfo['data'][0]['days'] * 24 * 60 * 60) + ($vdata['start_time']);
            $vdata['is_exchange'] = 0;
            $vdata['merchant_id'] = $typeWhere['merchant_id'];
            $vdata['`key`'] = $typeWhere['`key`'];
            $vdata['is_used'] = 0;
            $vdata['price'] = $price;
            $vdata['full_price'] = $typeInfo['data'][0]['full_price'];
            $vdata['user_id'] = yii::$app->session['user_id'];
            $transaction = Yii::$app->db->beginTransaction();
            $res_in = $voucherModel->add($vdata);
            if ($res_in['status'] != 200) {
                $transaction->rollBack();
                return result(500, "请求失败", $result);
            }
            //修改type里面的已发数量
            $send_count = $typeInfo['data'][0]['send_count'] + 1;
            $upWhere['id'] = $typeInfo['data'][0]['id'];
            $upWhere['send_count'] = $send_count;
            $res = $voucherTypeModel->update($upWhere);
            if ($res['status'] != 200) {
                $transaction->rollBack();
                return result(500, "请求失败", $result);
            }
            $transaction->commit();
            $result['receive_info']['start_time'] = date('Y-m-d', $vdata['start_time']);
            $result['receive_info']['end_time'] = date('Y-m-d', $vdata['end_time']);
            $result['receive_info']['price'] = $price;
            $result['receive_info']['pull_price'] = $vdata['full_price'];
            $re_user_info = $userModel->find(['id' => $vdata['user_id']]);
            $vdata['nickname'] = '';
            $vdata['avatar'] = '';
            if ($re_user_info['status'] == 200) {
                $vdata['nickname'] = $re_user_info['data']['nickname'];
                $vdata['avatar'] = $re_user_info['data']['avatar'];
                $vdata['id'] = $res_in['data'];
                $vdata['create_time'] = date('Y-m-d',time());
            }
            $vdata['is_licky'] = 0;
            if ($total == $info['data']['lucky_number']) {
                $vdata['is_licky'] = 1;
            }
            array_push($list,$vdata);
            $result['receive_list'] = $list;
            return result(200, "领取成功", $result);
        } else {
            return result(500, "请求方式错误");
        }
    }


}
