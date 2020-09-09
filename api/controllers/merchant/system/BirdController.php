<?php

namespace app\controllers\merchant\system;

use app\models\merchant\app\SystemAppAccessModel;
use app\models\merchant\system\BirdAccountModel;
use app\models\merchant\system\BirdOrderModel;
use app\models\shop\AfterInfoModel;
use app\models\shop\ContactModel;
use app\models\shop\GroupOrderModel;
use app\models\shop\OrderModel;
use app\models\shop\SubOrdersModel;
use yii;
use yii\web\MerchantController;

class BirdController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['notify'],//指定控制器不应用到哪些动作
            ]
        ];
    }

    public function actionOrder(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'order_sn'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }


            $birdAccountModel = new BirdAccountModel();
            $birdAccountInfo = $birdAccountModel->do_one([]);
            if ($birdAccountInfo['status'] != 200){
                return result(204, "未查询到蜂鸟配置信息");
            }
            $appId = $birdAccountInfo['data']['app_id'];

            $orderModel = new GroupOrderModel();
            $orderWhere['order_sn'] = $params['order_sn'];
            $orderInfo = $orderModel->one($orderWhere);
            if ($orderInfo['status'] != 200){
                return result(204, "未查询到订单信息");
            }
            if ($orderInfo['data']['express_type'] == 1){
                return result(204, "自提订单不能使用蜂鸟配送");
            }
            if ($orderInfo['data']['user_contact_id'] == 0){
                return result(204, "用户地址信息有误");
            }
            $contactModel = new ContactModel();
            $contactWhere['id'] = $orderInfo['data']['user_contact_id'];
            $contactInfo = $contactModel->find($contactWhere);
            if ($contactInfo['status'] != 200){
                return result(204, "用户地址信息有误");
            }
            $afterInfoModel = new AfterInfoModel();
            $afterInfoWhere['`key`'] = $params['key'];
            $afterInfo = $afterInfoModel->find($afterInfoWhere);
            if ($afterInfo['status'] != 200){
                return result(204, "未查询到商户信息");
            }
            $appModel = new SystemAppAccessModel();
            $appWhere['key'] = $params['key'];
            $appInfo = $appModel->do_one($appWhere);
            if ($appInfo['status'] != 200){
                return result(204, "未查询到应用信息");
            }
            if ($appInfo['data']['bird_is_open'] == 0){
                return result(204, "蜂鸟配送未开启");
            }
            if ($appInfo['data']['coordinate'] == ''){
                return result(204, "应用经纬度未设置");
            }
            $coordinate = explode(',',$appInfo['data']['coordinate']);
            $transportLongitude = $coordinate[0];
            $transportLatitude = $coordinate[1];
            $subOrdersModel = new SubOrdersModel();
            $subOrdersWhere['order_group_sn'] = $params['order_sn'];
            $goodsInfo = $subOrdersModel->do_select($subOrdersWhere);
            if ($goodsInfo['status'] != 200){
                return result(204, "未查询到订单商品信息");
            }
            $goods = [];
            $goodsCount = 0;
            foreach ($goodsInfo['data'] as $k=>$v){
                $goods['item_name'] = $v['name'];//商品名称(不超过128个字符)
                $goods['item_quantity'] = $v['number'];//商品数量
                $goods['item_price'] = $v['price'];//商品原价
                $goods['item_actual_price'] = $v['payment_money'];//商品实际支付金额，必须是乘以数量后的金额，否则影响售后环节的赔付标准
                $goods['is_need_package'] = 0;//是否需要ele打包 0:否 1:是
                $goods['is_agent_purchase'] = 0;//是否代购 0:否
                $dataArray['items_json'][] = $goods;
                $goodsCount = $goodsCount + $v['number'];
            }

            $dataArray['transport_info'] = array(
                'transport_name' => $appInfo['data']['name'],  //门店名称（支持汉字、符号、字母的组合），后期此参数将预留另用
                'transport_address' => $afterInfo['data']['after_addr'], //取货点地址，后期此参数将预留另用
                'transport_longitude' => $transportLongitude, //取货点经度，取值范围0～180，后期此参数将预留另用
                'transport_latitude' => $transportLatitude, //取货点纬度，取值范围0～90，后期此参数将预留另用
                'position_source' => 2, //取货点经纬度来源（1:腾讯地图, 2:百度地图, 3:高德地图），蜂鸟建议使用高德地图
                'transport_tel' => $afterInfo['data']['after_phone'], //取货点联系方式, 只支持手机号,400开头电话以及座机号码
                'transport_remark' => '备注' //取货点备注(非必填)
            );
            $dataArray['receiver_info'] = array(
                'receiver_name' => $orderInfo['data']['name'], //收货人姓名
                'receiver_primary_phone' => $orderInfo['data']['phone'], //收货人联系方式，只支持手机号，400开头电话，座机号码以及95013开头、长度13位的虚拟电话
                'receiver_address' => $orderInfo['data']['address'], //收货人地址
                'receiver_longitude' => $contactInfo['data']['longitude'], //收货人经度，取值范围0～180
                'receiver_latitude' => $contactInfo['data']['latitude'], //收货人纬度，取值范围0～90
                'position_source' => 1, //收货人经纬度来源（1:腾讯地图, 2:百度地图, 3:高德地图），蜂鸟建议使用高德地图
            );
            $dataArray['partner_order_code'] = $params['order_sn']; // 第三方订单号, 需唯一
            $dataArray['notify_url'] = 'http://ceshi.juanpao.cn/api/web/index.php/merchant/system/bird/notify';//第三方回调 url地址
            $dataArray['order_type'] = 1; //订单类型（1:即时单，3:预约单）
            $dataArray['chain_store_code'] = $birdAccountInfo['data']['chain_store_code']; //门店编号（支持数字、字母的组合）
            $dataArray['transport_name'] = $birdAccountInfo['data']['transport_name']; //门店名称（支持汉字、符号、字母的组合），后期此参数将预留另用
            $dataArray['order_total_amount'] = $orderInfo['data']['total_price'];//订单总金额（不包含商家的任何活动以及折扣的金额）
            $dataArray['order_actual_amount'] = $orderInfo['data']['payment_money'];//客户需要支付的金额
            $dataArray['is_invoiced'] = 0;//是否需要发票, 0:不需要, 1:需要
            $dataArray['order_payment_status'] = 1;//订单支付状态 0:未支付 1:已支付
            $dataArray['order_payment_method'] = 1; //订单支付方式 1:在线支付
            $dataArray['goods_count'] = $goodsCount; //订单货物件数
            $dataArray['is_agent_payment'] = 0; //是否需要ele代收 0:否

            $token = $this->requestToken();

            $salt = mt_rand(1000, 9999);
            $dataJson =  json_encode($dataArray, JSON_UNESCAPED_UNICODE);
            $urlencodeData = urlencode($dataJson);
            $sig = $this->generateBusinessSign($appId, $token, $urlencodeData, $salt);   //生成签名
            $requestJson = json_encode(array(
                'app_id' => $appId,
                'salt' => $salt,
                'data' => $urlencodeData,
                'signature' => $sig
            ));

            $url = 'https://open-anubis.ele.me/anubis-webapi/v2/order';
            $res = json_decode(curlPostJson($url,$requestJson),true);
            if ($res['code'] == 200){
                return result(200, "请求成功");
            }else{
                return result(500, $res['msg']);
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionNotify(){
        $result = urldecode(file_get_contents("php://input"));
        $res = urldecode(trim(json_decode(file_get_contents("php://input"),true)['data'],'"'));
        $res = json_decode($res,true);
        if ($res['order_status'] == 5){
            file_put_contents(Yii::getAlias('@webroot/') . '/log.text', date('Y-m-d H:i:s') . $result . PHP_EOL, FILE_APPEND);
            return;
        }
        $model = new BirdOrderModel();
        $where['order_sn'] = $res['partner_order_code'];
        $orderInfo = $model->do_one($where);
        if ($orderInfo['status'] == 204){
            $data['order_sn'] = $res['partner_order_code'];
            $data['open_order_code'] = $res['open_order_code'];
            $data['order_status'] = $res['order_status'];
            $data['push_time'] = $res['push_time'];
            $data['platform_code'] = $res['platform_code'];
            $data['tracking_id'] = $res['tracking_id'];
            $model->do_add($data);
            //修改商城订单状态
            $orderModel = new OrderModel();
            $orderWhere['order_sn'] = $res['partner_order_code'];
            $order = $orderModel->find($orderWhere);
            if ($order['status'] == 200) {
                $orderWhere['send_express_type'] = 0;  //实际发货方式 0=快递
                $orderWhere['express_id'] = 245; //system_express表中蜂鸟配送的ID
                $orderWhere['express_number'] = $res['open_order_code'];  //蜂鸟订单号
                if ($order['data']['is_tuan'] == 1) {
                    $type = 2;
                } else {
                    $type = 1;
                }
                $orderModel->updateSend($orderWhere,$type);
            }
        }elseif ($orderInfo['status'] == 200){
            $data['open_order_code'] = $res['open_order_code'];
            $data['order_status'] = $res['order_status'];
            $data['push_time'] = $res['push_time'];
            $data['carrier_driver_name'] = $res['carrier_driver_name'];
            $data['carrier_driver_phone'] = $res['carrier_driver_phone'];
            $data['platform_code'] = $res['platform_code'];
            $model->do_update($where,$data);
        }else{
            file_put_contents(Yii::getAlias('@webroot/') . '/log.text', date('Y-m-d H:i:s') . '数据库查询失败：' . $result . PHP_EOL, FILE_APPEND);
        }
        return;
    }

    private function requestToken() {
        $salt = mt_rand(1000, 9999);
        // 获取签名
        $birdAccountModel = new BirdAccountModel();
        $birdAccountInfo = $birdAccountModel->do_one([]);
        if ($birdAccountInfo['status'] != 200){
            return result(204, "未查询到蜂鸟配置信息");
        }
        $appId = $birdAccountInfo['data']['app_id'];
        $secretKey = $birdAccountInfo['data']['secret_key'];
        $sig = $this->generateSign($appId, $salt, $secretKey);
        $url = "https://open-anubis.ele.me/anubis-webapi/get_access_token?app_id={$appId}&salt={$salt}&signature={$sig}";
        $token = json_decode(curlGet($url), true)['data']['access_token'];
        return $token;
    }

    private function generateBusinessSign($appId, $token, $urlencodeData, $salt) {
        $seed = 'app_id=' . $appId . '&access_token=' . $token
            . '&data=' . $urlencodeData . '&salt=' . $salt;
        return md5($seed);
    }

    private function generateSign($appId, $salt, $secretKey) {
        $seed = 'app_id=' . $appId . '&salt=' . $salt . '&secret_key=' . $secretKey;
        return md5(urlencode($seed));
    }

    public function actionOne(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new BirdAccountModel();
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $array = $model->do_one($where);
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionAdd(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'app_id', 'secret_key', 'chain_store_code', 'transport_name'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $model = new BirdAccountModel();
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $info = $model->do_one($where);
            if ($info['status'] == 200){
                $data['app_id'] = $params['app_id'];
                $data['secret_key'] = $params['secret_key'];
                $data['chain_store_code'] = $params['chain_store_code'];
                $data['transport_name'] = $params['transport_name'];
                $array = $model->do_update($where,$data);
            }else{
                $data['key'] = $params['key'];
                $data['merchant_id'] = yii::$app->session['uid'];
                $data['app_id'] = $params['app_id'];
                $data['secret_key'] = $params['secret_key'];
                $data['chain_store_code'] = $params['chain_store_code'];
                $data['transport_name'] = $params['transport_name'];
                $array = $model->do_add($data);
            }

            return $array;

        } else {
            return result(500, "请求方式错误");
        }
    }


}