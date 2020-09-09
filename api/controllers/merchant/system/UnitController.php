<?php

namespace app\controllers\merchant\system;

use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\user\MerchantModel;
use yii;
use yii\web\MerchantController;
use yii\db\Exception;
use app\models\merchant\system\UnitModel;
use app\models\core\Base64Model;
use app\models\core\CosModel;
use WxPay\Wechat;
use app\models\merchant\app\AppAccessModel;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');

/**
 * 应用类目表控制器
 * 地址:/admin/rule
 * @throws Exception if the model cannot be found
 * @return array
 */
class UnitController extends MerchantController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['notify'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 地址:/admin/group/index 默认访问
     * @throws Exception if the model cannot be found
     * @return array
     */
    public function actionList() {
        if (yii::$app->request->isGet) {

            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new UnitModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            $params['fields'] = " title,route,expire_time ";
            $array = $model->findall($params);

            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionSingle() {

        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $model = new UnitModel();
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];

            //签到总开关查询
            if (isset($params['route']) && $params['route'] == "signIn") {
                $appAccessModel = new AppAccessModel();
                $appAccessInfo = $appAccessModel->find(['`key`' => $params['`key`'], 'merchant_id' => yii::$app->session['uid']]);
                if ($appAccessInfo['status'] == 200){
                    $array['id'] = $appAccessInfo['data']['id'];
                    $array['is_open'] = $appAccessInfo['data']['sign_in'];
                    return result(200, "请求成功",$array);
                } else {
                    return $appAccessInfo;
                }
            }

            $res = $model->find($params);
            if ($res['status'] != 200) {
                if (isset($params['route'])) {
                    if ($params['route'] == "copyright") {
                        $params['config'] = json_encode(yii::$app->params['copyright']);
                        $params['title'] = "自定义版权";
                        $array = $model->add($params);
                        if($array['status']==200){
                            $res['config'] = yii::$app->params['copyright'];
                            $res['id'] = $array['data'];
                            return result(200, "请求成功", $res);
                        }else{
                            return result(500, "请求失败");
                        }
                    } else if ($params['route'] == "integralMall") {
                        $params['config'] = json_encode(yii::$app->params['integralMall']);
                        $array = $model->add($params);
                        if($array['status']==200){
                            $params['title'] = "积分商城";
                            $res['config'] = yii::$app->params['integralMall'];
                            $res['id'] = $array['data'];
                            return result(200, "请求成功", $res);
                        }else{
                            return result(500, "请求失败" );
                        }
                    }else if ($params['route'] == "shansong") {
                        $params['config'] = "";
                        $params['title'] = "闪送";
                        $array = $model->add($params);
                        if($array['status']==200){
                            $res['config'] = "";
                            $res['id'] = $array['data'];
                            return result(200, "请求成功", $res);
                        }else{
                            return result(500, "请求失败" );
                        }
                    }
                    return $res;
                } else {
                    return result(500, "请求失败");
                }
            }
            $array['id'] = $res['data']['id'];
            $array['config'] = json_decode($res['data']['config'], true);
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

//    public function actionAdd() {
//        if (yii::$app->request->isPost) {
//            $request = yii::$app->request; //获取 request 对象
//            $params = $request->bodyParams; //获取body传参
//            $model = new UnitModel();
//
//            //设置类目 参数
//            $must = ['name'];
//            $rs = $this->checkInput($must, $params);
//            if ($rs != false) {
//                return $rs;
//            }
//
//            $params['`key`'] = $params['key'];
//            unset($params['key']);
//            $params['merchant_id'] = yii::$app->session['uid'];
//            if (isset($params['pic_url'])) {
//                if ($params['pic_url'] != "") {
//                    $base64 = new Base64Model();
//                    $path = creat_mulu('./uploads/forum/keywords');
//                    $localRes = $base64->base64_image_content($params['pic_url'], $path);
//                    $cos = new CosModel();
//                    $cosRes = $cos->putObject($localRes);
//                    if ($cosRes['status'] == '200') {
//                        $url = $cosRes['data'];
//                        unlink(Yii::getAlias('@webroot/') . $localRes);
//                    } else {
//                        unlink(Yii::getAlias('@webroot/') . $localRes);
//                        return json_encode($cosRes, JSON_UNESCAPED_UNICODE);
//                    }
//                    $params['pic_url'] = $url;
//                } else {
//                    unset($params['pic_url']);
//                }
//            }
//            $array = $model->add($params);
//            return $array;
//        } else {
//            return result(500, "请求方式错误");
//        }
//    }

    public function actionUpdate($id) {
        if (yii::$app->request->isPut) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new UnitModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                if ($params['route'] == "copyright") {
                    if (isset($params['pic_url'])) {
                        if ($params['pic_url'] != "") {
                            $params['config'] = json_encode($params['pic_url']);
                        }
                        unset($params['pic_url']);
                    }
                    $operationRecordData['module_name'] = '自定义版权';
                }
                if ($params['route'] == "domainName") {
                    $params['config'] = json_encode($params['text']);
                    unset($params['text']);
                }
                if ($params['route'] == "signIn") {
                    $where['sign_in'] = $params['status'];
                    unset($params['status']);
                }
                if ($params['route'] == "integralMall") {
                    $params['config'] = json_encode(yii::$app->params['integralMall']);
                }
                if ($params['route'] == "shansong") {
                    $data['md5'] = $params['md5'] ;
                    $data['m_id'] = $params['m_id'] ;
                    $data['partnerNo'] = $params['partnerNo'];
                    $data['token'] = $params['token'];
                    $data['mobile'] = $params['mobile'];
                    unset($params['md5']);
                    unset($params['mobile']);
                    unset($params['token']);
                    unset($params['partnerNo']);
                    unset($params['m_id']);
                    $params['config'] = json_encode($data);
                    $operationRecordData['module_name'] = '闪送';
                }

                if ($params['route'] == "signIn"){
                    $appAccessModel = new AppAccessModel();
                    $where['id'] = $id;
                    $res = $appAccessModel->update($where);
                    if ($res['status'] == 200){
                        //添加操作记录
                        $operationRecordModel = new OperationRecordModel();
                        $operationRecordData['key'] = $params['`key`'];
                        if (isset(yii::$app->session['sid'])) {
                            $subModel = new \app\models\merchant\system\UserModel();
                            $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                            if ($subInfo['status'] == 200){
                                $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                            }
                        } else {
                            $merchantModle = new MerchantModel();
                            $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                            if ($merchantInfo['status'] == 200) {
                                $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                            }
                        }
                        $operationRecordData['operation_type'] = '更新';
                        $operationRecordData['operation_id'] = $id;
                        $operationRecordData['module_name'] = '签到总开关';
                        $operationRecordModel->do_add($operationRecordData);
                    }
                    return $res;
                }
                $array = $model->update($params);
                if ($array['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $params['`key`'];
                    if (isset(yii::$app->session['sid'])) {
                        $subModel = new \app\models\merchant\system\UserModel();
                        $subInfo = $subModel->find(['id'=>yii::$app->session['sid']]);
                        if ($subInfo['status'] == 200){
                            $operationRecordData['merchant_id'] = $subInfo['data']['username'];
                        }
                    } else {
                        $merchantModle = new MerchantModel();
                        $merchantInfo = $merchantModle->find(['id'=>yii::$app->session['uid']]);
                        if ($merchantInfo['status'] == 200) {
                            $operationRecordData['merchant_id'] = $merchantInfo['data']['name'];
                        }
                    }
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $id;
                    $operationRecordModel->do_add($operationRecordData);
                }
                return $array;
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionDelete($id) {
        if (yii::$app->request->isDelete) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $model = new UnitModel();
            $params['id'] = $id;
            $params['`key`'] = $params['key'];
            unset($params['key']);
            $params['merchant_id'] = yii::$app->session['uid'];
            if (!isset($params['id'])) {
                return result(400, "缺少参数 id");
            } else {
                $array = $model->delete($params);
            }
            return $array;
        } else {
            return result(500, "请求方式错误");
        }
    }

    public function actionIndex() {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参
            $wx = new Wechat();
            $unit = yii::$app->params['unit'];
            try {
                $data['name'] = $unit[$params['route']]['title'];
                $data['money'] = $unit[$params['route']]['price'];
                $data['goos_tag'] = $unit[$params['route']]['title'];
                $data['trade_no'] = "unit_" . time();
                $data['attach'] = $params['key'] . "," . $params['route'];
                $data['notify_url'] = "http://api.juanpao.com/merchant/system/unit/notify";
                $config = json_decode(json_encode(yii::$app->params['wx_config']), false);
                $result = $wx->wxPayUnifiedOrder($data, $config);
            } catch (\Exception $e) {

                return result(500, "二维码获取失败");
            }
            $array = [
                // 'data' => 'http://192.168.188.12/pay/wechat/qrcode?data=' . $result,
                'out_trade_no' => $data['trade_no'],
                'data' => 'http://api.juanpao.com/pay/wechat/qrcode?data=' . $result,
            ];
            return result(200, "请求成功", $array);
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 订单查询
     * @throws \Exception
     */
    public function actionQuery() {
        $request = yii::$app->request; //获取 request 对象
        $params = $request->get(); //获取地址栏参数
        $must = ['out_trade_no'];
        $checkRes = $this->checkInput($must, $params);
        if ($checkRes != false) {
            return $checkRes;
        }
        //获取商户微信配置

        $config = $config = json_decode(json_encode(yii::$app->params['wx_config']), false);
        //执行微信请求
        $wx = new Wechat();
        try {
            $result = $wx->orderQuery($params['out_trade_no'], $config);
        } catch (\Exception $e) {

            return result(500, "内部错误");
        }
        if ($result['return_code'] == 'FAIL') {
            return result(500, "请求参数错误", $result['return_msg']);
        }
        if ($result['result_code'] == 'FAIL') {
            $array = [
                'err_code' => $result['err_code'],
                'err_code_des' => $result['err_code_des'],
            ];
            return result(500, "查询失败", $array);
        }
        if ($result['trade_state'] == "SUCCESS") {
            $arr = [
                'out_trade_no' => $result['out_trade_no'], //订单编号
                'total_fee' => $result['total_fee'], //标价金额
                'trade_state' => $result['trade_state'], //交易状态
                'trade_state_desc' => $result['trade_state_desc'], //交易状态描述
                'trade_type' => $result['trade_type'], //交易类型
            ];
        } else {
            $arr = [
                'out_trade_no' => $result['out_trade_no'], //订单编号          
                'trade_state' => $result['trade_state'], //交易状态
            ];
        }
        return result(200, "请求成功", $arr);
    }

    public function actionNotify() {
        //获取商户微信配置
        $xml = file_get_contents("php://input");
        $wxPatNotify = new \WxPayNotify();
        $wxPatNotify->Handle(false);
        $returnValues = $wxPatNotify->GetValues();
        $result = $wxPatNotify->FromXml($xml);


        if (!empty($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            //商户逻辑处理，如订单状态更新为已支付
            $unit = yii::$app->params['unit'];
            $res = explode(",", $result['attach']);

            $appAccessModel = new AppAccessModel();
            $appAccess = $appAccessModel->find(['`key`' => $res[0]]);

            $model = new UnitModel();
            $config = "";
            if ($res[1] == "copyright") {
                $config = json_encode(yii::$app->params['copyright']);
            }
            if ($res[1] == "domainName") {
                $config = json_encode(yii::$app->params['domainName']);
            }
            if ($res[1] == "signIn") {
                $config = json_encode(yii::$app->params['signIn']);
            }
            if ($res[1] == "integralMall") {
                $config = json_encode(yii::$app->params['integralMall']);
            }
            $params = array(
                '`key`' => $res[0],
                'title' => $unit[$res[1]]['title'],
                'pic_url' => $unit[$res[1]]['pic_url'],
                'merchant_id' => $appAccess['data']['merchant_id'],
                'route' => $res[1],
                'config' => $config,
                'expire_time' => strtotime(date('Y-m-d', strtotime("+ 1year"))),
                'status' => 1,
            );
            $rs = $model->add($params);
        }
        if ($rs['status'] == 200) {
            ob_clean();
            echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
            die();
        } else {
            ob_clean();
            echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
            die();
        }
    }

    /**
     * @param $log_content
     */
    private function logger($log_content) {
        if (isset($_SERVER['HTTP_APPNAME'])) {   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        } else if ($_SERVER['REMOTE_ADDR'] != "127.0.0.1") { //LOCAL
            $max_size = 1000000;
            $log_filename = "log.xml";
            if (file_exists($log_filename) and ( abs(filesize($log_filename)) > $max_size)) {
                unlink($log_filename);
            }
            file_put_contents($log_filename, date('Y-m-d H:i:s') . " " . $log_content . "\r\n", FILE_APPEND);
        }
    }

}
