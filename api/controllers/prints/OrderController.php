<?php

namespace app\controllers\prints;

use app\models\shop\ContactModel;
use app\models\shop\SubOrdersModel;
use app\models\system\SystemMerchantMiniAccessModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateAccessModel;
use app\models\system\SystemMerchantMiniSubscribeTemplateModel;
use yii;
use yii\db\Exception;
use yii\web\MerchantController;
use app\models\shop\OrderModel;
use app\models\shop\ElectronicsModel;
use app\models\shop\SystemExpressModel;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class OrderController extends MerchantController
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function actionPrints()
    {

        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key', 'electronics_id'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            if ($params['electronics_id'] == 0){
                return result(500, "本地发货,无法打印电子面单");
            }

            $model = new OrderModel();
            $eModel = new ElectronicsModel();

            $eData = $eModel->do_one(['id' => $params['electronics_id']]);
            if ($eData['status'] != 200) {
                return result(500, "请求失败");
            }
            $expressModel = new SystemExpressModel();
            $express = $expressModel->find(['id' => $eData['data']['express_id']]);
            if (!is_array($params['order_sn'])) {
                return result(500, "请选择订单编号");
            }

            for ($i = 0; $i < count($params['order_sn']); $i++) {
                $arr = [];
                for ($j = 0; $j < count($params['order_sn'][$i]); $j++) {
                    if ($j == 0) {
                        $order = $model->find(['order_sn' => $params['order_sn'][$i][$j]]);
                        $eorder['ShipperCode'] = $express['data']['simple_name'];
                        if ($express['data']['simple_name'] == "ZJS") {
                            $eorder["LogisticCode"] = $params['LogisticCode'];
                        }
                        //物流公司信息
                        $eorder["ThrOrderCode"] = $params['order_sn'][$i][$j];
                        $eorder["OrderCode"] = date("Y-m-d H:i:s", time()) . rand(1000, 9999);
                        $eorder['IsReturnPrintTemplate'] = 1;
                        $eorder["PayType"] = 1;
                        $eorder["ExpType"] = 1;
                        if ($express['data']['simple_name'] != "SF") {
                            $eorder["CustomerName"] = $eData['data']['customer_name'];
                            $eorder["CustomerPwd"] = $eData['data']['customer_pwd'];
                            $eorder['MonthCode'] = $eData['data']['month_code'];
                            $eorder['SendSite'] = $eData['data']['dot_code'];
                            $eorder['SendStaff'] = $eData['data']['dot_name'];
                        }

                        //发件人信息
                        $sender["Name"] = $eData['data']['name'];
                        $sender["Mobile"] = $eData['data']['phone'];
                        $sender["ProvinceName"] = $eData['data']['province_name'];
                        $sender["CityName"] = $eData['data']['city_name'];
                        $sender["ExpAreaName"] = $eData['data']['area_name'];
                        $sender["Address"] = $eData['data']['addr'];
                        $sender["PostCode"] = $eData['data']['post_code'];

                        //收件人信息
                        $contactModel = new ContactModel();
                        $contactWhere['id'] = $order['data']['user_contact_id'];
                        $contactInfo = $contactModel->find($contactWhere);
                        if ($contactInfo['status'] != 200){
                            return result(500, "订单_{$params['order_sn'][$i][$j]}用户信息有误");
                        }

                        $receiver["Name"] = $order['data']['name'];
                        $receiver["Mobile"] = $order['data']['phone'];
                        $receiver["ProvinceName"] = $contactInfo['data']['province'];
                        $receiver["CityName"] = $contactInfo['data']['city'];
                        $receiver["ExpAreaName"] = $contactInfo['data']['area'];
                        $receiver["Address"] = $contactInfo['data']['address'];
                        $receiver["PostCode"] = $contactInfo['data']['postcode'];

                        $commodityOne = [];
                        $commodityOne["GoodsName"] = $eData['data']['towing_goods'];
                        $commodity = [];
                        $commodity[] = $commodityOne;

                        $temp = electronics($eorder, $sender, $receiver, $commodity);
                        if ($temp['Success'] == false){
                            return result(500, "订单_{$params['order_sn'][$i][$j]},{$temp['Reason']}");
                        }
                        $arr['PrintTemplate'] = $temp['PrintTemplate'];
                        $arr['express_number'] = $temp['Order']['LogisticCode'];
                        $arr['order_sn'] = $params['order_sn'][$i][$j];
                    }
                }
                $res[$i] = $arr;
            }

            return result(200, "请求成功",$res);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 商城后台，订单管理，主订单列表
     * 地址:/admin/group/index 默认访问
     * @return array
     */
    public function actionList()
    {
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            $model = new OrderModel();
            $params['shop_order_group.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_order_group.merchant_id'] = yii::$app->session['uid'];
            if (yii::$app->session['sid'] != null) {
                $params['shop_order_group.supplier_id'] = yii::$app->session['sid'];
            }

            $array = $model->findAllPirnt($params);

            if ($params['status'] == 1 && $array['status'] == 200) {
                foreach ($array['data'] as $key => $val) {
                    $res[$val['user_id'] . $val['phone'] . $val['name'] . $val['address']][] = $val;
                }
                $array['data'] = [];
                $count = 0;
                foreach ($res as $k => $v) {
                    $array['data'][] = $v;
                    $count++;
                }
                $array['count'] = $count;
            }

            //查询结果中data数据有时会变为对象，重新拼装处理
            if ($params['status'] != 1 && $array['status'] == 200){
                $re = [];
                foreach ($array['data'] as $key => $val) {
                    $re[] = $val;
                }
                $array['data'] = $re;
            }

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

            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }

            $model = new OrderModel();
            $params['shop_order_group.`key`'] = $params['key'];
            unset($params['key']);
            $params['shop_order_group.merchant_id'] = yii::$app->session['uid'];
            $params['shop_order_group.id'] = $id;
            unset($params['id']);
            $array = $model->findAll($params);
            if ($array['status'] == 200) {
                unset($array['count']);
                $array['data'] = $array['data'][0];
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSend()
    {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参


            $must = ['key'];
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return json_encode($rs, JSON_UNESCAPED_UNICODE);
            }
            if (!isset($params['express_number'][0]) || empty($params['express_number'][0])){
                return result(500, "快递单号不能为空");
            }
            if ($params['electronics_id'] == 0){
                $express['data']['name'] = "本地配送";
                $eData['data']['express_id'] = 0;
            }else{
                $eModel = new ElectronicsModel();
                $eData = $eModel->do_one(['id' => $params['electronics_id']]);
                if ($eData['status'] != 200){
                    return result(500, "未查询到电子面单信息");
                }
                $expressModel = new SystemExpressModel();
                $express = $expressModel->find(['id' => $eData['data']['express_id']]);
                if ($express['status'] != 200){
                    return result(500, "未查询到快递公司信息");
                }
            }
            $model = new OrderModel();
            if (isset($params['key'])) {
                $data['`key`'] = $params['key'];
                unset($params['key']);
            }
            $data['merchant_id'] = yii::$app->session['uid'];
            $str = "";
            for ($i = 0; $i < count($params['order_sn']); $i++) {
                if ($i == 0) {
                    $str = $params['order_sn'][$i];
                } else {
                    $str = $str . "," . $params['order_sn'][$i];
                }
            }

            $res = $model->findList(["order_sn in ({$str})" => null, 'merchant_id' => $data['merchant_id']]);

            if ($res['status'] != 200) {
                return result(500, "请求失败");
            }
            $bool = true;

            for ($i = 0; $i < count($res['data']); $i++) {
                if ($res['data'][$i]['status'] != 1) {
                    $bool = false;
                    break;
                }
            }
            if ($bool == false) {
                return result(500, "请核对订单状态");
            }
//            if (count($params['order_sn']) != count($params['express_number'])) {
//                return result(500, "参数错误");
//            }
            for ($i = 0; $i < count($params['order_sn']); $i++) {
                $data['order_sn'] = $params['order_sn'][$i];
                if ($params['electronics_id'] == 0){
                    $data['send_express_type'] = 1;  //实际发货方式
                }else{
                    $data['send_express_type'] = 0;  //实际发货方式
                }
                $data['express_number'] = $params['express_number'][0];
                $data['express_id'] = $eData['data']['express_id'];
                $data['status'] = 3;
                if ($params['is_tuan'][$i] == 1) {
                    $array = $model->updateSend($data, 2);
                } else {
                    $array = $model->updateSend($data);
                }
                //发货成功再处理微信消息
                if ($array['status'] == 200){
                    $orderModel = new OrderModel;
                    $orderRs = $orderModel->find(['order_sn' => $params['order_sn'][$i]]);

                    $shopUserModel = new \app\models\shop\UserModel();
                    $shopUser = $shopUserModel->find(['id' => $orderRs['data']['user_id']]);

                    $tempModel = new \app\models\system\SystemMiniTemplateModel();
                    $minitemp = $tempModel->do_one(['id' => 29]);
                    //单号,金额,下单时间,物品名称,
                    $tempParams = array(
                        'keyword1' => $params['express_number'][0],
                        'keyword2' => date("Y-m-d h:i:sa", time()),
                        'keyword3' => $orderRs['data']['create_time'],
                        'keyword4' => $orderRs['data']['goodsname'],
                    );

                    $tempAccess = new SystemMerchantMiniAccessModel();
                    $taData = array(
                        'key' => $orderRs['data']['key'],
                        'merchant_id' => $orderRs['data']['merchant_id'],
                        'mini_open_id' => $shopUser['data']['mini_open_id'],
                        'template_id' => 29,
                        'number' => '0',
                        'template_params' => json_encode($tempParams),
                        'template_purpose' => 'order',
                        'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn'][$i]}",
                        'status' => '-1',
                    );
                    $tempAccess->do_add($taData);

                    //订阅消息
                    $subscribeTempModel = new SystemMerchantMiniSubscribeTemplateModel();
                    $subscribeTempInfo = $subscribeTempModel->do_one(['template_purpose'=>'send_goods']);
                    if ($subscribeTempInfo['status'] == 200){
                        if ($params['electronics_id'] == 0){  //本地配送
                            $params['express_number'][0] = 0;
                        }
                        if (mb_strlen($orderRs['data']['goodsname'],'utf-8') > 20){
                            $goodsName = mb_substr($orderRs['data']['goodsname'],0,17,'utf-8') .'...'; //商品名超过20个汉字截断
                        }else{
                            $goodsName = $orderRs['data']['goodsname'];
                        }
                        $accessParams = array(
                            'character_string1' => ['value'=>$params['order_sn'][$i]],  //订单号
                            'thing2' => ['value'=>$goodsName],  //商品名
                            'thing8' => ['value'=>$express['data']['name']],    //快递公司
                            'character_string9' => ['value'=>$params['express_number'][0]],   //快递单号
                        );
                        $subscribeTempAccessModel = new SystemMerchantMiniSubscribeTemplateAccessModel();
                        $subscribeTempAccessData = array(
                            'key' => $orderRs['data']['key'],
                            'merchant_id' => $orderRs['data']['merchant_id'],
                            'mini_open_id' => $shopUser['data']['mini_open_id'],
                            'template_id' => $subscribeTempInfo['data']['template_id'],
                            'number' => '0',
                            'template_params' => json_encode($accessParams, JSON_UNESCAPED_UNICODE),
                            'template_purpose' => 'send_goods',
                            'page' => "/pages/orderItem/orderItem/orderItem?order_sn={$params['order_sn'][$i]}",
                            'status' => '-1',
                        );
                        $subscribeTempAccessModel->do_add($subscribeTempAccessData);
                    }
                }
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

}
