<?php

namespace app\controllers\merchant\system;

use yii;
use yii\web\MerchantController;
use app\models\merchant\system\UuAccountModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\shop\AfterInfoModel;
use app\models\shop\GroupOrderModel;
use app\models\merchant\system\UuOrderModel;
use app\models\shop\OrderModel;
use app\models\shop\UserModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\shop\ScoreModel;
use app\models\shop\SubOrderModel;
use app\models\merchant\app\AppAccessModel;

class UuController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionList(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new UuAccountModel();
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_one($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionOrderlist(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new UuOrderModel();
            $params['merchant_id'] = yii::$app->session['uid'];
            if (isset($params['searchName'])) {
                if ($params['searchName'] != "") {
                    $params['order_sn'] = ['like', "{$params['searchName']}"];
                }
                unset($params['searchName']);
            }
            $array = $model->do_select($params);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'appid', 'appkey', 'openid'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $params['merchant_id'] = yii::$app->session['uid'];

            $model = new UuAccountModel();
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $res = $model->do_one($where);

            if ($res['status'] == 200){
                $array = $model->do_update(['id'=>$res['data']['id']],$params);
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $res['data']['id'];
                    $operationRecordData['module_name'] = 'UU跑腿';
                    $operationRecordModel->do_add($operationRecordData);
                }
            }else{
                $array = $model->do_add($params);
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '新增';
                    $operationRecordData['operation_id'] = $array['data'];
                    $operationRecordData['module_name'] = 'UU跑腿';
                    $operationRecordModel->do_add($operationRecordData);
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //计算订单价格
    public function actionGetorderprice(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'order_sn'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            //查询商户UU配置
            $accountWhere['key'] = $params['key'];
            $account = $this->getconfig($accountWhere);
            if ($account['status'] != 200){
                return result(500, '未查询到UU配置');
            }

            //查询收件人地址
            $orderModel = new GroupOrderModel();
            $orderWhere['shop_order_group.order_sn'] = $params['order_sn'];
            $sonWhere['field'] = "shop_user_contact.*";
            $orderWhere['join'][] = ['left join', 'shop_user_contact', 'shop_order_group.user_contact_id = shop_user_contact.id'];;
            $order = $orderModel->do_select($orderWhere);
            if ($order['status'] != 200){
                return result(500, '未查到订单信息');
            }

            $addressInfo[0] = $order['data'][0]['province'];
            $addressInfo[1] = $order['data'][0]['city'];
            $addressInfo[2] = $order['data'][0]['area'];
            $addressInfo[3] = $order['data'][0]['address'];

            //查询商户门店地址，暂用退货地址
            $afterModel = new AfterInfoModel();
            $afterWhere['`key`'] = $params['key'];
            $afterWhere['merchant_id'] = yii::$app->session['uid'];
            $after = $afterModel->find($afterWhere);
            if ($after['status'] != 200){
                return result(500, '未查到商户地址信息');
            }
            //收件地址与发件地址要在同一城市
            if ($addressInfo[1] != $after['data']['city']){
                return result(500, "UU跑腿不支持跨城市配送");
            }
            //获取已开通UU跑腿业务城市列表进行对比
            $accountInfo['appid'] = $account['data']['appid'];
            $accountInfo['appkey'] = $account['data']['appkey'];
            $cityInfo = $this->getcitylist($accountInfo);
            if ($cityInfo['return_code'] != 'ok' || !isset($cityInfo['CityList'])){
                return result(500, $cityInfo['return_msg']);
            }
            foreach ($cityInfo['CityList'] as $k=>$v){
                $cityList[] = $v['CityName'];
            }
            if (!in_array($after['data']['city'],$cityList)){
                return result(500, "该城市暂未开通UU跑腿业务");
            }
            //获取UU账户余额
            $accountInfo['openid'] = $account['data']['openid'];
            $balanceInfo = $this->getbalancedetail($accountInfo);
            if ($balanceInfo['return_code'] != 'ok' || !isset($balanceInfo['AccountMoney'])){
                return result(500, $cityInfo['return_msg']);
            }

            $guid = strtolower(str_replace('-', '', $this->guid()));
            $data = [
                'origin_id'=>$params['order_sn'],  //第三方对接平台订单id
                'from_address'=>$addressInfo[3],  //起始地址
                'to_address'=>$after['data']['address'],  //目的地址
                'city_name'=>$addressInfo[1],  //订单所在城市名 称(如郑州市就填”郑州市“，必须带上“市”)
//                'subscribe_type'=>$params['subscribe_type'],  //预约类型 0实时订单 1预约取件时间(非必传)
                'to_lat'=>'0',   //目的地坐标纬度，如果无，传0(坐标系为百度地图坐标系)
                'to_lng'=>'0',   //目的地坐标经度，如果无，传0(坐标系为百度地图坐标系)
                'from_lat'=>'0',   //起始地坐标纬度，如果无，传0(坐标系为百度地图坐标系)
                'from_lng'=>'0',   //起始地坐标经度，如果无，传0(坐标系为百度地图坐标系)
                'send_type'=>'0',    //订单小类 0帮我送(默认) 1帮我买
                'nonce_str'=>$guid,  //随机字符串，不长于32位
                'timestamp'=>time(),  //时间戳，以秒计算时间，即unix-timestamp
                'openid'=>$account['data']['openid'],
                'appid'=>$account['data']['appid'],
            ];

//            if (!empty($params['subscribe_time'])){
//                $data['subscribe_time'] = $params['subscribe_time']; //预约时间（如：2015-06-18 12:00:00）没有可以传空字符串(非必传)
//            }
            ksort($data);
            $data['sign'] = $this->sign($data, $account['data']['appkey']);  //加密签名，详情见 消息体签名算法
            $url = 'http://openapi.uupaotui.com/v2_0/getorderprice.ashx';
            $res = json_decode(curlPost($url,$data),true);
//            var_dump($res);
            if ($res['return_code'] == 'ok'){
                $array['price_token'] = $res['price_token'];
                $array['total_money'] = $res['total_money'];
                $array['need_paymoney'] = $res['need_paymoney'];
                $array['AccountMoney'] = $balanceInfo['AccountMoney'];
                $array['order_sn'] = $params['order_sn'];
                return result(200, "请求成功",$array);
            }else{
                return result(500, $res['return_msg']);
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    //发布订单
    public function actionAddorder(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'order_sn', 'price_token', 'order_price', 'balance_paymoney', 'special_type', 'callme_withtake'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            //查询商户UU配置
            $accountWhere['key'] = $params['key'];
            $account = $this->getconfig($accountWhere);
            if ($account['status'] != 200){
                return result(500, '未查询到UU配置');
            }

            //查询收件人信息,
            $receiverModel = new GroupOrderModel();
            $receiverWhere['order_sn'] = $params['order_sn'];
            $receiver = $receiverModel->one($receiverWhere);
            if ($receiver['status'] != 200){
                return result(500, '未查询到订单信息');
            }
            if (empty($receiver['data']['name']) || empty($receiver['data']['phone'])){
                return result(500, '收件人信息有误');
            }

            $guid = strtolower(str_replace('-', '', $this->guid()));
            $data = [
                'price_token'=>$params['price_token'],  //金额令牌，计算订单价格接口返回的price_token
                'order_price'=>$params['order_price'],  //订单金额，计算订单价格接口返回的total_money
                'balance_paymoney'=>$params['balance_paymoney'],  //实际余额支付金额计算订单价格接口返回的need_paymoney
                'receiver'=>$receiver['data']['name'],  //收件人
                'receiver_phone'=>$receiver['data']['phone'],  //收件人电话 手机号码； 虚拟号码格式（手机号_分机号码）例如：13700000000_1111
                'callback_url'=>'https://api.juanpao.com/merchantuucallback',  //订单提交成功后及状态变化的回调地址(非必传)
                'push_type'=>'0',  //推送方式（0 开放订单，2测试订单）默认传0即可
                'special_type'=>$params['special_type'],  //特殊处理类型，是否需要保温箱 1需要 0不需要
                'callme_withtake'=>$params['callme_withtake'],  //取件是否给我打电话 1需要 0不需要
                'nonce_str'=>$guid,  //随机字符串，不长于32位
                'timestamp'=>time(),  //时间戳，以秒计算时间，即unix-timestamp
                'openid'=>$account['data']['openid'],
                'appid'=>$account['data']['appid'],
            ];

            if (!empty($params['note'])){
                $data['note'] = $params['note']; //订单备注 最长140个汉字(非必传)
            }

            ksort($data);
            $data['sign'] = $this->sign($data, $account['data']['appkey']);  //加密签名，详情见 消息体签名算法
            $url = 'http://openapi.uupaotui.com/v2_0/addorder.ashx';
            $res = json_decode(curlPost($url,$data),true);
//            var_dump($res);die;
            //UU下单成功，添加UU订单记录、修改商城订单状态,否则返回失败
            if ($res['return_code'] == 'ok'){
                //添加UU订单记录
                $uuOrderModel = new UuOrderModel();
                $uuOrderData = [
                    'key'=>$params['key'],
                    'merchant_id'=>yii::$app->session['uid'],
                    'order_sn'=>$params['order_sn'],
                    'ordercode'=>$res['ordercode'],
                    'user_id'=>$receiver['data']['user_id'],
                    'user_name'=>$receiver['data']['name'],
                    'user_phone'=>$receiver['data']['phone'],
                    'address'=>$receiver['data']['address'],
                    'status'=>'1',
                ];
                $uuOrderModel->do_add($uuOrderData);

                //修改商城订单状态
                $orderModel = new OrderModel();
                $orderWhere['`key`'] = $params['key'];
                $orderWhere['merchant_id'] = yii::$app->session['uid'];
                $orderWhere['order_sn'] = $params['order_sn'];
                $order = $orderModel->find($orderWhere);
                if ($order['status'] == 200) {
                    $orderWhere['send_express_type'] = 0;  //实际发货方式 0=快递
                    $orderWhere['express_id'] = 243; //system_express表中UU跑腿的ID
                    $orderWhere['express_number'] = $res['ordercode'];  //UU跑腿订单号
                    if ($order['data']['is_tuan'] == 1) {
                        $type = 2;
                    } else {
                        $type = 1;
                    }
                    $orderModel->updateSend($orderWhere,$type);
                }
                //小程序消息推送
                $userModel = new UserModel();
                $shopUser = $userModel->find(['id' => $order['data']['user_id']]);
                $tempParams = array(
                    'keyword1' => $orderWhere['express_number'],
                    'keyword2' => date("Y-m-d h:i:sa", time()),
                    'keyword3' => $order['data']['create_time'],
                    'keyword4' => $order['data']['goodsname'],
                );
                $tempAccess = new SystemMerchantMiniAccessModel();
                $taData = array(
                    'key' => $order['data']['key'],
                    'merchant_id' => $order['data']['merchant_id'],
                    'mini_open_id' => $shopUser['data']['mini_open_id'],
                    'template_id' => 29,
                    'number' => '0',
                    'template_params' => json_encode($tempParams),
                    'template_purpose' => 'order',
                    'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn']}",
                    'status' => '-1',
                );
                $tempAccess->do_add($taData);

                //添加操作记录
                $operationRecordModel = new OperationRecordModel();
                $operationRecordData['key'] = $params['key'];
                $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                $operationRecordData['operation_type'] = '更新';
                $operationRecordData['operation_id'] = $params['order_sn'];
                $operationRecordData['module_name'] = '订单管理';
                $operationRecordModel->do_add($operationRecordData);

                return result(200, '请求成功');
            }else{
                return result(500, $res['return_msg']);
            }


        } else {
            return result(500, "请求方式错误");
        }
    }

    //取消订单
    public function actionCancelorder($id){
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'order_sn', 'order_code'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            //查询商户UU配置
            $accountWhere['key'] = $params['key'];
            $account = $this->getconfig($accountWhere);
            if ($account['status'] != 200){
                return $account;
            }

            $guid = strtolower(str_replace('-', '', $this->guid()));
            $data = [
                'order_code'=>$params['order_code'],  //UU跑腿订单编号，order_code和origin_id必须二选其一，如果都传，则只根据order_code返回
                'origin_id'=>$params['order_sn'],  //第三方对接平台订单id，order_code和origin_id必须二选其一，如果都传，则只根据order_code返回
                'nonce_str'=>$guid,  //随机字符串，不长于32位
                'timestamp'=>time(),  //时间戳，以秒计算时间，即unix-timestamp
                'openid'=>$account['data']['openid'],
                'appid'=>$account['data']['appid'],
            ];
            if (empty($params['reason'])){
                $data['reason'] = '无'; //取消原因
            }else{
                $data['reason'] = $params['reason']; //取消原因
            }
            ksort($data);
            $data['sign'] = $this->sign($data, $account['data']['appkey']);  //加密签名，详情见 消息体签名算法
            $url = 'http://openapi.uupaotui.com/v2_0/cancelorder.ashx';
            $res = json_decode(curlPost($url,$data),true);
            $res['return_code'] = 'ok';
            if ($res['return_code'] == 'ok'){
                $uuModel = new UuOrderModel();
                $uuWhere['id'] = $id;
                $uuData['status'] = '-1';
                $array = $uuModel->do_update($uuWhere,$uuData);
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $params['order_sn'];
                    $operationRecordData['module_name'] = 'UU订单管理';
                    $operationRecordModel->do_add($operationRecordData);
                }
                return $array;
            }else{
                return result(500, $res['return_msg']);
            }


        } else {
            return result(500, "请求方式错误");
        }
    }

    //回调
    public function actionCallback(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $uuModel = new UuOrderModel();
            $uuWhere['ordercode'] = $params['order_code'];
            if ($params['state'] == '3'){
                $uuData = [
                    'driver_name'=>$params['driver_name'],
                    'driver_jobnum'=>$params['driver_jobnum'],
                    'driver_mobile'=>$params['driver_mobile'],
                    'driver_photo'=>$params['driver_photo'],
                    'status'=>$params['state'],
                ];
            }elseif ($params['state'] == '10'){
                $uuData['status'] = $params['state'];

                //客户收件后，请修改商城订单信息，此处逻辑从 PUT shopOrderGoods方法复制过来的
                $uuOrderInfo = $uuModel->do_one($uuWhere);
                if ($uuOrderInfo['status'] = 200){
                    //主订单修改
                    $model = new OrderModel();
                    $data['`key`'] = $uuOrderInfo['data']['key'];
                    $data['merchant_id'] = $uuOrderInfo['data']['merchant_id'];
                    $data['user_id'] = $uuOrderInfo['data']['user_id'];
                    $data['order_sn'] = $uuOrderInfo['data']['order_sn'];
                    $data['status'] = 6;
                    $model->update($data);
                    //订单关联表修改
                    $subOrder = new SubOrderModel();
                    $sub['`key`'] = $uuOrderInfo['data']['key'];
                    $sub['merchant_id'] = $uuOrderInfo['data']['merchant_id'];
                    $sub['user_id'] = $uuOrderInfo['data']['user_id'];
                    $sub['order_group_sn'] = $uuOrderInfo['data']['order_sn'];
                    $sub['confirm_time'] = time();
                    $subOrder->update($sub);
                    //vip权益
                    $sql = "select is_vip,vip_validity_time from shop_user where id = " . $uuOrderInfo['data']['user_id'];
                    $vipUser = $subOrder->querySql($sql);
                    $vip = 1;
                    if ($vipUser[0]['is_vip'] == 1 && $vipUser[0]['vip_validity_time'] > time()) {
                        $sql = "select score_times from shop_vip_config where merchant_id = " . $uuOrderInfo['data']['merchant_id'] . " `key` = '" . $uuOrderInfo['data']['key'] . "'";
                        $vipConfig = $subOrder->querySql($sql);
                        if (count($vipConfig) != 0) {
                            $vip = $vipConfig[0]['score_times'];
                        }
                    }
                    $rs = $model->tableSingle("shop_order_group", ['order_sn' => $uuOrderInfo['data']['order_sn'], 'delete_time is null' => null]);
                    $scoreModel = new ScoreModel();

                    $scoreData = array(
                        '`key`' => $uuOrderInfo['data']['key'],
                        'merchant_id' => $uuOrderInfo['data']['merchant_id'],
                        'user_id' => $uuOrderInfo['data']['user_id'],
                        'score' => $rs['payment_money'] * $vip,
                        'content' => '购买商品送积分',
                        'type' => '1',
                        'status' => '1'
                    );
                    $scoreModel->add($scoreData);

                    $configModel = new \app\models\tuan\ConfigModel();

                    $config = $configModel->do_one(['merchant_id' => $uuOrderInfo['data']['merchant_id'], 'key' => $uuOrderInfo['data']['key']]);
                    if ($config['status'] == 200 && $config['data']['status'] == 1) {
                        //团长佣金
                        $balanceModel = new \app\models\shop\BalanceModel();
                        $balance = $balanceModel->do_one(['order_sn' => $uuOrderInfo['data']['order_sn'], 'type' => 1, 'key' => $uuOrderInfo['data']['key'], 'merchant_id' => $uuOrderInfo['data']['merchant_id']]);
                        if ($balance['status'] == 200) {
                            $userModel = new UserModel();
                            $user = $userModel->find(['id' => $balance['data']['uid']]);
                            if ($user['status'] == 200) {
                                $userModel->update(['id' => $balance['data']['uid'], '`key`' => $uuOrderInfo['data']['key'], 'balance' => (float)$user['data']['balance'] + (float)$balance['data']['money']]);
                            }
                        }
                    }
                    //供应商金额
                    $subBalanceModel = new \app\models\system\SystemSubAdminBalanceModel();
                    $subBalance = $subBalanceModel->do_select(['order_sn' => $uuOrderInfo['data']['order_sn']]);
                    if ($subBalance['status'] == 200) {
                        $subBalanceModel->do_update(['order_sn' => $uuOrderInfo['data']['order_sn']], ['status' => 1]);
                        for ($i = 0; $i < count($subBalance['data']); $i++) {
                            $subUserModel = new \app\models\merchant\system\UserModel();
                            $sql = "update system_sub_admin set balance = balance+{$subBalance['data'][$i]['money']} where id = {$subBalance['data'][$i]['sub_admin_id']}";
                            $subUserModel->querySql($sql);
                        }
                    }


                    $orderModel = new OrderModel;
                    $orderRs = $orderModel->find(['order_sn' => $uuOrderInfo['data']['order_sn']]);

                    $shopUserModel = new \app\models\shop\UserModel();
                    $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);

                    $tempModel = new \app\models\system\SystemMiniTemplateModel();
                    $minitemp = $tempModel->do_one(['id' => 32]);
                    //单号,金额,下单时间,物品名称,
                    // [{"keyword_id":"1","name":"订单号","example":"201703158237869"},
                    //{"keyword_id":"3","name":"完成时间","example":"2017-03-22 10:04:12"},
                    //{"keyword_id":"5","name":"订单号码","example":"201703158237869"},
                    //{"keyword_id":"12","name":"联系电话","example":"13899990000"},
                    $tempParams = array(
                        'keyword1' => $uuOrderInfo['data']['order_sn'],
                        'keyword2' => $orderRs['data']['update_time'],
                        'keyword3' => $orderRs['data']['create_time'],
                        'keyword4' => $orderRs['data']['phone'],
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
                        'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$uuOrderInfo['data']['order_sn']}",
                        'status' => '-1',
                    );
                    $tempAccess->do_add($taData);
                }


            }else{
                $uuData['status'] = $params['state'];
            }
            //修改uu订单表状态
            $array = $uuModel->do_update($uuWhere,$uuData);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //获取已开通uu账户余额
    function getbalancedetail($params){
        $guid = strtolower(str_replace('-', '', $this->guid()));
        $data = [
            'nonce_str'=>$guid,
            'timestamp'=>time(),
            'openid'=>$params['openid'],
            'appid'=>$params['appid'],
        ];

        ksort($data);
        $data['sign'] = $this->sign($data, $params['appkey']);  //加密签名，详情见 消息体签名算法
        $url = 'http://openapi.uupaotui.com/v2_0/getbalancedetail.ashx';
        $res = json_decode(curlPost($url,$data),true);
        return $res;
    }

    //获取UU配置
    function getconfig($params){
        if (empty($params['key'])){
            return result(500, "未传参数");
        }
        $appModel = new AppAccessModel();
        $appWhere['`key`'] = $params['key'];
        $appWhere['merchant_id'] = yii::$app->session['uid'];
        $array = $appModel->find($params);
        if ($array['status'] != 200){
            return result(500, "未查询到该应用配置");
        }

        if ($array['data']['uu_is_open'] == 0){
            return result(500, "UU跑腿未开启");
        }

        //查询商户UU配置
        $model = new UuAccountModel();
        $accountWhere['key'] = $params['key'];
        $accountWhere['merchant_id'] = yii::$app->session['uid'];
        $account = $model->do_one($accountWhere);
        return $account;
    }


    //获取已开通uu服务城市
    function getcitylist($params){
        $guid = strtolower(str_replace('-', '', $this->guid()));
        $data = [
            'nonce_str'=>$guid,
            'timestamp'=>time(),
            'appid'=>$params['appid'],
        ];

        ksort($data);
        $data['sign'] = $this->sign($data, $params['appkey']);  //加密签名，详情见 消息体签名算法
        $url = 'http://openapi.uupaotui.com/v2_0/getcitylist.ashx';
        $res = json_decode(curlPost($url,$data),true);
        return $res;
    }

    // 生成guid
    function guid(){
        if (function_exists('com_create_guid')){
            return com_create_guid();
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
            return $uuid;
        }
    }

    // 生成签名
    function sign($data, $appKey) {
        $arr = [];
        foreach ($data as $key => $value) {
            $arr[] = $key.'='.$value;
        }

        $arr[] = 'key='.$appKey;
        $str = strtoupper(implode('&', $arr));
        return strtoupper(md5($str));
    }

}