<?php

namespace app\controllers\merchant\system;

use yii;
use yii\web\MerchantController;
use app\models\admin\app\AppAccessModel;
use app\models\merchant\system\DianWoDaModel;
use app\models\merchant\system\DianWoDaOrderModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\system\UuAccountModel;
use app\models\shop\AfterInfoModel;
use app\models\shop\OrderModel;

class DianWoDaController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    private $gateway = "https://open.dianwoda.com/gateway";
    private $appkey;
    private $app_secret;
    private $access_token;

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

            $model = new DianWoDaModel();
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

            $must = ['key', 'appkey', 'appsecret', 'token'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $data['key'] = $params['key'];
            $data['merchant_id'] = yii::$app->session['uid'];
            $data['appkey'] = $params['appkey'];
            $data['appsecret'] = $params['appsecret'];
            $data['accesstoken'] = $params['token'];

            $model = new DianWoDaModel();
            $where['key'] = $params['key'];
            $where['merchant_id'] = yii::$app->session['uid'];
            $res = $model->do_one($where);

            if ($res['status'] == 200){
                $array = $model->do_update(['id'=>$res['data']['id']],$data);
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $res['data']['id'];
                    $operationRecordData['module_name'] = '点我达';
                    $operationRecordModel->do_add($operationRecordData);
                }
            }else{
                $array = $model->do_add($data);
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '新增';
                    $operationRecordData['operation_id'] = $array['data'];
                    $operationRecordData['module_name'] = '点我达';
                    $operationRecordModel->do_add($operationRecordData);
                }
            }

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    //下单
    public function actionCreate(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'order_sn'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            //查询应用名称
            $appModel = new AppAccessModel();
            $appInfo = $appModel->find(['`key`'=>$params['key'],'merchant_id'=>yii::$app->session['uid']]);
            if ($appInfo['status'] != 200){
                return $appInfo;
            }
            if ($appInfo['data']['dianwoda_is_open'] == 0){
                return result(500, '点我达未开启');
            }
            $coordinate = explode(",",$appInfo['data']['coordinate']);
            $sellerLat = $coordinate[1];
            $sellerLng = $coordinate[0];

            //获取点我达配置
            $dianwodaModel = new DianWoDaModel();
            $dianwodaWhere['key'] = $params['key'];
            $dianwodaWhere['merchant_id'] = yii::$app->session['uid'];
            $dianwodaInfo = $dianwodaModel->do_one($dianwodaWhere);
            if ($dianwodaInfo['status'] != 200){
                return $dianwodaInfo;
            }
            $this->appkey = $dianwodaInfo['data']['appkey'];
            $this->app_secret = $dianwodaInfo['data']['appsecret'];
            $this->access_token = $dianwodaInfo['data']['accesstoken'];

            //查询商户地址
            $afterModel = new AfterInfoModel();
            $afterWhere['`key`'] = $params['key'];
            $afterWhere['merchant_id'] = yii::$app->session['uid'];
            $after = $afterModel->find($afterWhere);
            if ($after['status'] != 200){
                return $after;
            }

            //获取城市行政区划代码
            $cityList = (array)$this->request('dianwoda.data.city.code',NULL);
            if ($cityList['code'] == 'success'){
                $cityInfo = (array)$cityList['data'];
                foreach ($cityInfo['cities'] as $k=>$v){
                    $city = (array)$v;
                    if ($after['data']['city'] == $city['city_name']){
                        $cityCode = $city['city_code'];
                    }
                }
                if (!isset($cityCode)){
                    return result(500, '不支持城市');
                }

            }else{
                if (isset($cityList['sub_message'])){
                    return result(500, $cityList['sub_message']);
                }
                return result(500, $cityList['message']);
            }

            //获取订单信息
            $orderModel = new OrderModel();
            $sql = "SELECT sog.name,sog.phone,suc.address,suc.latitude,suc.longitude 
                    FROM `shop_order_group` AS sog 
                    LEFT JOIN `shop_user_contact` AS suc ON sog.user_contact_id = suc.id 
                    WHERE sog.order_sn = {$params['order_sn']}";
            $orderInfo = $orderModel->querySql($sql);
            if (count($orderInfo) <= 0){
                return result(500, '未查到订单信息');
            }

            //获取订单运费
            $freightData = array(
                "city_code" => $cityCode,  //行政区划代码
                "seller_id" => $params['key'],  //客户系统中的门店编号，门店的唯一性标识
                "seller_name" => $appInfo['data']['name'],  //门店名称
                "seller_mobile" => $after['data']['after_phone'],  //门店联系电话
                "seller_address" => $after['data']['after_addr'],  //门店文本地址
                "seller_lat" => $sellerLat,  //门店纬度坐标
                "seller_lng" => $sellerLng,  //门店经度坐标
                "consignee_address" => $orderInfo[0]['address'],  //收货人地址
                "consignee_lat" => $orderInfo[0]['latitude'],  //收货人纬度坐标
                "consignee_lng" => $orderInfo[0]['longitude'],  //收货人经度坐标
                "cargo_weight" => 0  //订单商品重量，单位：克。如果无，默认传0
            );
            $res = (array)$this->request('dianwoda.order.cost.estimate',$freightData);
            if ($res['code'] == 'success'){
                $res = (array)$res['data'];
                $totalPrice = $res['total_price'];
            }else{
                if (isset($response['sub_message'])){
                    return result(500, $res['sub_message']);
                }
                return result(500, $res['message']);
            }

            $data = array(
                "order_original_id" => $params['order_sn'],  //商户订单编号
                "order_create_time" => getMillisecond(),  //商户订单创建时间戳，毫秒级
                "order_price" => $totalPrice,  //订单金额(分)
                "city_code" => $cityCode,  //行政区划代码
                "seller_id" => $params['key'],  //客户系统中的门店编号，门店的唯一性标识
                "seller_name" => $appInfo['data']['name'],  //门店名称
                "seller_mobile" => $after['data']['after_phone'],  //门店联系电话
                "seller_address" => $after['data']['after_addr'],  //门店文本地址
                "seller_lat" => $sellerLat,  //门店纬度坐标
                "seller_lng" => $sellerLng,  //门店经度坐标
                "consignee_name" => $orderInfo[0]['name'],  //收货人姓名
                "consignee_mobile" => $orderInfo[0]['phone'],  //收货人手机号码
                "consignee_address" => $orderInfo[0]['address'],  //收货人地址
                "consignee_lat" => $orderInfo[0]['latitude'],  //收货人纬度坐标
                "consignee_lng" => $orderInfo[0]['longitude'],  //收货人经度坐标
                "cargo_weight" => 0,  //订单商品重量，单位：克。如果无，默认传0
                "cargo_num" => 1  //商品件数（默认传1）
            );


            $response = (array)$this->request('dianwoda.order.create',$data);

            if ($response['code'] == 'success'){
                $array = (array)$response['data'];
                $dianwodaOrderModel = new DianWoDaOrderModel();
                $orderData['key'] = $params['key'];
                $orderData['merchant_id'] = yii::$app->session['uid'];
                $orderData['order_sn'] = $params['order_sn'];
                $orderData['dwd_order_id'] = $array['dwd_order_id'];
                $orderData['total_price'] = $totalPrice;
                if (isset($array['distance'])){
                    $orderData['distance'] = $array['distance'];
                }
                if (isset($array['price'])){
                    $orderData['price'] = $array['price'];
                }
                if (isset($array['skycon'])){
                    $orderData['skycon'] = $array['skycon'];
                }
                $dianwodaOrderModel->do_add($orderData);

                //修改商城订单状态
                $orderWhere['`key`'] = $params['key'];
                $orderWhere['merchant_id'] = yii::$app->session['uid'];
                $orderWhere['order_sn'] = $params['order_sn'];
                $order = $orderModel->find($orderWhere);
                if ($order['status'] == 200) {
                    $orderWhere['send_express_type'] = 0;  //实际发货方式 0=快递
                    $orderWhere['express_id'] = 244;  //system_express表中点我达的ID
                    $orderWhere['express_number'] = $array['dwd_order_id'];  //点我达订单号
                    if ($order['data']['is_tuan'] == 1) {
                        $type = 2;
                    } else {
                        $type = 1;
                    }
                    $orderModel->updateSend($orderWhere,$type);
                }

                return result(200, "请求成功");
            }else{
                if (isset($response['sub_message'])){
                    return result(500, $response['sub_message']);
                }
                return result(500, $response['message']);
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    //生成签名
    private function sign( $appkey, $timestamp, $nonce, $access_token, $api, $secret, $biz_params ) {
        $unsignRequestParams = array(
            'appkey' => $appkey,
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'api' => $api
        );
        if($access_token != NULL) {
            $unsignRequestParams["access_token"] = $access_token;
        }
        ksort($unsignRequestParams);
        $unsignStr = '';
        foreach ($unsignRequestParams as $key => $value){
            $unsignStr = $unsignStr.$key.'='.$value.'&';
        }
        $unsignStr = $unsignStr.'body='.$biz_params;
        $unsignStr = $unsignStr.'&secret='.$secret;
        return sha1($unsignStr);
    }

    //接口请求
    function request($api, $apiParam) {
        $bodyStr = $apiParam == NULL ? "" : json_encode($apiParam);
        $timestamp = getMillisecond();
        $nonce = mt_rand(0, 1000000);
        $sign = $this->sign($this->appkey,$timestamp,$nonce,$this->access_token,$api,$this->app_secret,$bodyStr);

        $url = $this->gateway.'?appkey='.$this->appkey.'&timestamp='.$timestamp.'&nonce='.$nonce.'&access_token='.$this->access_token.'&api='.$api.'&sign='.$sign;

        $options = array(
            'http' => array(
                'header'  => "Content-type: application/json; charset=utf-8",
                'method'  => 'POST',
                'content' => $bodyStr
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if($result === FALSE){
            throw new Exception("请求gateway异常");
        }

        return json_decode($result);
    }



}