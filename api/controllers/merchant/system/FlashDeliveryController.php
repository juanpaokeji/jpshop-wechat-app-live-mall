<?php

namespace app\controllers\merchant\system;

use app\models\merchant\app\AppAccessModel;
use app\models\merchant\system\OperationRecordModel;
use app\models\merchant\system\UnitModel;
use app\models\shop\AfterInfoModel;
use app\models\system\ShanSongModel;
use yii;
use app\controllers\merchant\design\MaterialController;

class FlashDeliveryController extends MaterialController {

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors() {
        return [
            'token' => [
                'class' => 'yii\filters\MerchantFilter', //调用过滤器
//                'only' => ['single'],//指定控制器应用到哪些动作
                'except' => ['syncstatus'], //指定控制器不应用到哪些动作
            ]
        ];
    }


    //计算费用
    public function actionCalc(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','order'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            //查询闪送查询是否开启
            $appAccessModel = new AppAccessModel();
            $appAccessInfo = $appAccessModel->find(['`key`' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
            if ($appAccessInfo['status'] == 200){
                if ($appAccessInfo['data']['shansong'] == 0){
                    return result(500, "闪送插件未开启");
                }
            } else {
                return $appAccessInfo;
            }

            //查询商户闪送配置
            $unitModel = new UnitModel();
            $unitWhere['key'] = $params['key'];
            $unitWhere['route'] = 'shansong';
            $unitInfo = $unitModel->find($unitWhere);
            if ($unitInfo['status'] != 200){
                return result(500, "无闪送配置信息");
            }

            //查询应用名称
            $appAccessModel = new AppAccessModel();
            $appInfo = $appAccessModel->find(['`key`'=>$params['key']]);
            if ($appInfo['status'] != 200){
                return result(500, "未查询到该应用");
            }

            //查询寄件地址，暂用商户退件地址
            $afterInfoModel = new AfterInfoModel();
            $afterInfo = $afterInfoModel->find(['`key`'=>$params['key']]);
            if ($afterInfo['status'] != 200){
                return result(500, "未查询到寄件地址");
            }

            $config = json_decode($unitInfo['data']['config'],true);
            $signature = strtoupper(md5($config['partnerNo'].$params['order']['orderNo'].$config['mobile'].$config['md5']));

            $params['order']['merchant'] = [
                'id'=>$config['m_id'],
                'mobile'=>$config['mobile'],
                'name'=>$appInfo['data']['name'],
                'token'=>$config['token'],
            ];
            $params['partnerNo'] = $config['partnerNo'];
            $params['signature'] = $signature;
            unset($params['key']);

            $url = "http://open.ishansong.com/openapi/order/v3/calc";

            $data = json_encode($params, JSON_UNESCAPED_UNICODE);

            $rs = curlPostJson($url, $data);

            $rs = json_decode($rs,true);
            if ($rs['status'] == 'OK'){
                return result(200, "请求成功",$rs['data']);
            }else{
                return result(500, $rs['errMsg']);
            }
        } else {
            return result(500, "请求方式错误");
        }

    }

    //下单
    public function actionSave(){
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->bodyParams; //获取body传参

            $must = ['key','order'];
            //设置类目 参数
            $rs = $this->checkInput($must, $params);
            if ($rs != false) {
                return $rs;
            }

            $shanSongData['key'] = $params['key'];
            //查询闪送查询是否开启
            $appAccessModel = new AppAccessModel();
            $appAccessInfo = $appAccessModel->find(['`key`' => $params['key'], 'merchant_id' => yii::$app->session['uid']]);
            if ($appAccessInfo['status'] == 200){
                if ($appAccessInfo['data']['shansong'] == 0){
                    return result(500, "闪送插件未开启");
                }
            } else {
                return $appAccessInfo;
            }

            //查询商户闪送配置
            $unitModel = new UnitModel();
            $unitWhere['key'] = $params['key'];
            $unitWhere['route'] = 'shansong';
            $unitInfo = $unitModel->find($unitWhere);
            if ($unitInfo['status'] != 200){
                return result(500, "无闪送配置信息");
            }

            //查询应用名称
            $appAccessModel = new AppAccessModel();
            $appInfo = $appAccessModel->find(['`key`'=>$params['key']]);
            if ($appInfo['status'] != 200){
                return result(500, "未查询到该应用");
            }

            //查询寄件地址，暂用商户退件地址
            $afterInfoModel = new AfterInfoModel();
            $afterInfo = $afterInfoModel->find(['`key`'=>$params['key']]);
            if ($afterInfo['status'] != 200){
                return result(500, "未查询到寄件地址");
            }

            $config = json_decode($unitInfo['data']['config'],true);
            $signature = strtoupper(md5($config['partnerNo'].$params['order']['orderNo'].$config['mobile'].$config['md5']));

            $params['order']['merchant'] = [
                'id'=>$config['m_id'],
                'mobile'=>$config['mobile'],
                'name'=>$appInfo['data']['name'],
                'token'=>$config['token'],
            ];
            $params['partnerNo'] = $config['partnerNo'];
            $params['signature'] = $signature;
            unset($params['key']);
//            $url = "http://open.s.bingex.com/openapi/order/v3/save";  //测试环境
            $url = "http://open.ishansong.com/openapi/order/v3/save";  //线上
            $data = json_encode($params, JSON_UNESCAPED_UNICODE);
            $rs = curlPostJson($url, $data);
            $rs = json_decode($rs,true);
            if ($rs['status'] == 'OK'){
                //增加本地记录
                $shanSongModel = new ShanSongModel();
                $shanSongData['merchant_id'] = yii::$app->session['uid'];
                $shanSongData['order_sn'] = $params['order']['orderNo'];
                $shanSongData['iss_order_sn'] = $rs['data'];
                $shanSongData['addition'] = $params['order']['addition'];
                $shanSongData['weight'] = $params['order']['weight'];
                $shanSongData['appointTime'] = $params['order']['appointTime'];
                $shanSongData['sender'] = json_encode($params['order']['sender'], JSON_UNESCAPED_UNICODE);
                $shanSongData['receiverList'] = json_encode($params['order']['receiverList'], JSON_UNESCAPED_UNICODE);
                $res = $shanSongModel->do_add($shanSongData);
                if ($res['status'] == 200){
                    //添加操作记录
                    $operationRecordModel = new OperationRecordModel();
                    $operationRecordData['key'] = $unitWhere['key'];
                    $operationRecordData['merchant_id'] = yii::$app->session['uid'];
                    $operationRecordData['operation_type'] = '更新';
                    $operationRecordData['operation_id'] = $params['order']['orderNo'];
                    $operationRecordData['module_name'] = '订单管理';
                    $operationRecordModel->do_add($operationRecordData);
                    return result(200, "下单成功");
                } else {
                    return result(500, "下单失败");
                }
            }else{
                return result(500, $rs['errMsg']);
            }

        } else {
            return result(500, "请求方式错误");
        }
    }

    //查询订单
    public function actionInfo(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $id = 'SS8850';
            $token = 'Es5ZpQrw/J4mnmSg2zLNdtApN4jYTJMftYK6CiALJKE=';
//            $partnerNo = '8850';
            $partnerNo = '1099';
            $orderNo = '2017083120170832';
//            $mobile = '18961303123';
            $mobile = '13301061589';
            $key = 'hfsi0g69pplp'; //hfsi0g69pplp //12345abcde
            $str = $partnerNo.$orderNo.$mobile.$key;
//            $signature = strtoupper(md5($str));
            $signature = 'A79AE26C5043FC4D6B0DFC1AD96CA7BD';
            $issorderno = 'TDH2017083119524158';

            $url = "http://open.s.bingex.com/openapi/order/v3/info?partnerno=$partnerNo&orderno=$orderNo&mobile=$mobile&signature=$signature&issorderno=$issorderno";
//            $url = "http://open.ishansong.com/openapi/order/v3/info?partnerno=$partnerNo&orderno=$orderNo&mobile=$mobile&signature=$signature&issorderno=$issorderno";  //线上


            $rs = curlGet($url);
            $rs = json_decode($rs,true);
            return $rs;

        } else {
            return result(500, "请求方式错误");
        }
    }

    //取消订单
    public function actionCancel(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数

            $id = 'SS8850';
            $token = 'Es5ZpQrw/J4mnmSg2zLNdtApN4jYTJMftYK6CiALJKE=';
//            $partnerNo = '8850';
            $partnerNo = '1099';
            $orderNo = '2017083120170832';
//            $mobile = '18961303123';
            $mobile = '13301061589';
            $key = 'hfsi0g69pplp'; //hfsi0g69pplp //12345abcde
            $str = $partnerNo.$orderNo.$mobile.$key;
//            $signature = strtoupper(md5($str));
            $signature = 'A79AE26C5043FC4D6B0DFC1AD96CA7BD';
            $issorderno = 'TDH2017083119524158';

            $url = "http://open.s.bingex.com/openapi/order/v3/cancel?partnerno=$partnerNo&orderno=$orderNo&mobile=$mobile&signature=$signature&issorderno=$issorderno";
//            $url = "http://open.ishansong.com/openapi/order/v3/cancel?partnerno=$partnerNo&orderno=$orderNo&mobile=$mobile&signature=$signature&issorderno=$issorderno";  //线上


            $rs = curlGet($url);
            $rs = json_decode($rs,true);
            return $rs;



        } else {
            return result(500, "请求方式错误");
        }
    }

    //查询订单轨迹
    public function actionTrail(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数


            $id = 'SS8850';
            $token = 'Es5ZpQrw/J4mnmSg2zLNdtApN4jYTJMftYK6CiALJKE=';
//            $partnerNo = '8850';
            $partnerNo = '1099';
            $orderNo = '2017083120170832';
//            $mobile = '18961303123';
            $mobile = '13301061589';
            $key = 'hfsi0g69pplp'; //hfsi0g69pplp //12345abcde
            $str = $partnerNo.$orderNo.$mobile.$key;
//            $signature = strtoupper(md5($str));
            $signature = 'A79AE26C5043FC4D6B0DFC1AD96CA7BD';
            $issorderno = 'TDH2017083119524158';

            $url = "http://open.s.bingex.com/openapi/order/v3/trail?partnerno=$partnerNo&orderno=$orderNo&mobile=$mobile&signature=$signature&issorderno=$issorderno";
//            $url = "http://open.ishansong.com/openapi/order/v3/trail?partnerno=$partnerNo&orderno=$orderNo&mobile=$mobile&signature=$signature&issorderno=$issorderno"; //线上


            $rs = curlGet($url);
            $rs = json_decode($rs,true);
            return $rs;


        } else {
            return result(500, "请求方式错误");
        }
    }

    //查询账户余额
    public function actionAccount(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数
//            var_dump(555);die;
            $partnerNo = '8850';
//            $partnerNo = '1099';
            $mobile = '18961303123';
//            $mobile = '13301061589';

            $url = "http://open.s.bingex.com/openapi/order/v3/account?partnerno=$partnerNo&mobile=$mobile";
//            $url = "http://open.ishansong.com/openapi/order/v3/account?partnerno=$partnerNo&mobile=$mobile"; //线上


            $rs = curlGet($url);
            $rs = json_decode($rs,true);
            return result(200, "请求成功",$rs);

        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 此接口用于商户回调
     * 状态推送接口
     */
    public function actionSyncstatus(){
        if (yii::$app->request->isGet) {
            $request = yii::$app->request; //获取 request 对象
            $params = $request->get(); //获取地址栏参数





        } else {
            return result(500, "请求方式错误");
        }
    }


}