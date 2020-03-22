<?php

/**
 * Created by 卷泡
 * author: wmy
 */

namespace app\controllers\tencents;

use app\models\admin\user\MerchantModel;
use app\models\tencents\TencentsModel;
use yii;
use yii\web\Controller;


class InstanceController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    /**
     * @return array
     * @throws yii\db\Exception
     */
    public function actionInstance()
    {
        if (yii::$app->request->isPost) {
            $request = yii::$app->request; //获取 request 对象
            $urlParams = $request->get(); //获取地址栏参数
            //校验签名
            if (!self::checkSignature($urlParams['signature'], $urlParams['timestamp'], $urlParams['eventId'])) {
                file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . '签名错误check_token' . PHP_EOL, FILE_APPEND);
                return result(500, "签名错误");
            };
            $request_body = file_get_contents('php://input');
            $data = json_decode($request_body, true);
            switch ($data['action']) {
                case 'verifyInterface': //token和接口url校验接
                    $array['echoback'] = $data['echoback'];
                    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    return $array;
                    break;
                case 'createInstance': //实例创建通知接口
                    return $this->CreateInstance($data, $request_body);
                    break;
                case 'renewInstance': //实例续费通知接口
                    return $this->RenewInstance($data, $request_body);
                    break;
                case 'modifyInstance'://实例配置变更通知接口
                    return $this->ModifyInstance($data, $request_body);
                    break;
                case 'expireInstance': //实例过期通知接口
                    return $this->ExpireInstance($data, $request_body);
                    break;
                case 'destroyInstance': //实例销毁通知接口
                    return $this->DestroyInstance($data, $request_body);
                    break;
                default:
                    return ['success' => 'true'];
            }
        } else {
            return result(500, "请求方式错误");
        }
    }

    /**
     * 创建实例
     * @param $data
     * @param $request_body
     * @return array
     * @throws yii\db\Exception
     */
    public function CreateInstance($data, $request_body)
    {
        //查询是否购买根据openId查询
        $tcsModel = new TencentsModel();
        $logData = $tcsModel->one(['openId' => $data['openId'],'productId' => $data['productId']]);
        if ($logData['status'] == 200) {
            //先存个日志 tencent_instance_log
            $insData['action'] = $data['action'];
            $insData['orderId'] = $data['orderId'];
            $insData['openId'] = $data['openId'];
            $insData['productId'] = $data['productId'];
            $insData['requestId'] = '';
            $insData['info'] = $request_body;
            $insData['merchant_id'] = $logData['data']['merchant_id'];
            $insData['remark'] = '再次购买';
            $is_pay = 0;
            if($data['productInfo']['isTrail'] === 'true' || $data['productInfo']['isTrail'] === true){
                $is_pay = 1;
            }
            $insData['is_pay'] = $is_pay;
            $res = $tcsModel->add($insData);
            if ($res['status'] != 200) {
                file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . '_tcsModel_' . json_encode($insData) . PHP_EOL, FILE_APPEND);
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['signId' => 0];
            }
            //返回参数
            $appInfo['website'] = 'https://www.juanpao.com';
            $additionalInfo[0]['name'] = '您已经有账号请联系客服';
            $additionalInfo[0]['value'] = '522585535';
            $additionalInfo[1]['name'] = '登录网址:';
            $additionalInfo[1]['value'] = 'https://www.juanpao.com';
            $result['signId'] = 0;
            $result['appInfo'] = json_encode($appInfo);
            $result['additionalInfo'] = json_encode($additionalInfo);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $result;
        } else {
            //先存个日志 tencent_instance_log
            $insData['action'] = $data['action'];
            $insData['orderId'] = $data['orderId'];
            $insData['openId'] = $data['openId'];
            $insData['productId'] = $data['productId'];
            $insData['requestId'] = '';
            $insData['info'] = $request_body;
            $insData['remark'] = '首次试用';
            $is_pay = 0;
            if($data['productInfo']['isTrail'] === 'true' || $data['productInfo']['isTrail'] === true){
                $is_pay = 1;
                $insData['remark'] = '首次购买';
            }
            $insData['is_pay'] = $is_pay;
            $res = $tcsModel->add($insData);
            if ($res['status'] != 200) {
                file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . '_tcsModel_' . json_encode($insData) . PHP_EOL, FILE_APPEND);
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['signId' => 0];
            }
            //创建一个账户
            $params['salt'] = $this->get_randomstr(32);
            $phone = rand(100000, 999999) . $res['data'];
            $data = [
                'password' => md5('123456' . $params['salt']),
                'salt' => $params['salt'],
                'phone' => $phone,
                'status' => 1,
                'source' => 1,
                'create_time' => time(),
            ];
            $merchantModel = new MerchantModel();
            $array = $merchantModel->add($data);
            if ($array['status'] != 200) {
                file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . '_merchantModel_' . json_encode($data) . PHP_EOL, FILE_APPEND);
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['signId' => 0];
            }
            //修改tencent_instance_log
            $up_res = $tcsModel->do_update(['id' => $res['data']], ['merchant_id' => $array['data']]);
            if ($up_res['status'] != 200) {
                file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . '_merchantModel_tencent_instance_log_id' . $res['data'] . '----merchant_id' . $array['data'] . PHP_EOL, FILE_APPEND);
                \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['signId' => 0];
            }
            //返回参数
            $appInfo['website'] = 'https://www.juanpao.com';
            $additionalInfo[0]['name'] = '账号信息';
            $additionalInfo[0]['value'] = '账号:' . $phone . ',密码:123456';
            $additionalInfo[1]['name'] = '登录网址:';
            $additionalInfo[1]['value'] = 'https://www.juanpao.com';
            $additionalInfo[2]['name'] = '专属客服:';
            $additionalInfo[2]['value'] = '微信号:liqianye3123,电话:18961303123，QQ:77721811';
            $additionalInfo[3]['name'] = '温馨提示:';
            $additionalInfo[3]['value'] = '登录后请尽快修改密码,使用教程:https://www.juanpao.com/help/pc/index.html,如需帮助请联系客服人员。';
            $additionalInfo[4]['name'] = '付费版请注意:';
            $additionalInfo[4]['value'] = '购买付费版的客户，请立即联系我们的客服，开通对应版本';
            $result['signId'] = 0;
            $result['appInfo'] = json_encode($appInfo);
            $result['additionalInfo'] = json_encode($additionalInfo);
            \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $result;
        }
    }

    /**
     * 实例续费通知接口
     * @param $data
     * @param $request_body
     * @return array
     */
    public function RenewInstance($data, $request_body)
    {
        //先存个日志 tencent_instance_log
        $tcsModel = new TencentsModel();
        $logData = $tcsModel->one(['openId' => $data['openId'],'productId' => $data['productId']]);
        if ($logData['status'] != 200) {
            file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . 'RenewInstance_logData_' . $request_body . PHP_EOL, FILE_APPEND);
        }
        $insData['action'] = $data['action'];
        $insData['orderId'] = $data['orderId'];
        $insData['openId'] = $data['openId'];
        $insData['productId'] = $data['productId'];
        $insData['requestId'] = '';
        $insData['info'] = $request_body;
        $insData['merchant_id'] = $logData['data']['merchant_id'];
        $insData['remark'] = '续费';
        $insData['is_pay'] = 1;
        $res = $tcsModel->add($insData);
        if ($res['status'] != 200) {
            file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . 'RenewInstance_res_' . json_encode($insData) . PHP_EOL, FILE_APPEND);
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['success' => 'true'];
    }

    /**
     * 实例配置变更通知接口
     * @param $data
     * @param $request_body
     * @return array
     */
    public function ModifyInstance($data, $request_body)
    {
        //先存个日志 tencent_instance_log
        $tcsModel = new TencentsModel();
        $logData = $tcsModel->one(['openId' => $data['openId'],'productId' => $data['productId']]);
        if ($logData['status'] != 200) {
            file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . 'ModifyInstance_logData_' . $request_body . PHP_EOL, FILE_APPEND);
        }
        $insData['action'] = $data['action'];
        $insData['orderId'] = $data['orderId'];
        $insData['openId'] = $data['openId'];
        $insData['productId'] = $data['productId'];
        $insData['requestId'] = '';
        $insData['info'] = $request_body;
        $insData['merchant_id'] = $logData['data']['merchant_id'];
        $insData['remark'] = '配置变更';
        $insData['is_pay'] = 1;
        $res = $tcsModel->add($insData);
        if ($res['status'] != 200) {
            file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . 'ModifyInstance_res_' . json_encode($insData) . PHP_EOL, FILE_APPEND);
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['success' => 'true'];
    }

    /**
     * 实例过期通知接口
     * @param $data
     * @param $request_body
     * @return array
     */
    public function ExpireInstance($data, $request_body)
    {
        //先存个日志 tencent_instance_log
        $tcsModel = new TencentsModel();
        $logData = $tcsModel->one(['openId' => $data['openId'],'productId' => $data['productId']]);
        if ($logData['status'] != 200) {
            file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . 'ExpireInstance_logData_' . $request_body . PHP_EOL, FILE_APPEND);
        }
        $insData['action'] = $data['action'];
        $insData['orderId'] = '';
        $insData['openId'] = $data['openId'];
        $insData['productId'] = $data['productId'];
        $insData['requestId'] = '';
        $insData['info'] = $request_body;
        $insData['merchant_id'] = $logData['data']['merchant_id'];
        $insData['remark'] = '实例过期';
        $res = $tcsModel->add($insData);
        if ($res['status'] != 200) {
            file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . 'ExpireInstance_res_' . json_encode($insData) . PHP_EOL, FILE_APPEND);
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['success' => 'true'];
    }

    /**
     * 实例销毁通知接口
     * @param $data
     * @param $request_body
     * @return array
     */
    public function DestroyInstance($data, $request_body)
    {
        //先存个日志 tencent_instance_log
        $tcsModel = new TencentsModel();
        $logData = $tcsModel->one(['openId' => $data['openId'],'productId' => $data['productId']]);
        if ($logData['status'] != 200) {
            file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . 'DestroyInstance_logData_' . $request_body . PHP_EOL, FILE_APPEND);
        }
        $insData['action'] = $data['action'];
        $insData['orderId'] = $data['orderId'];
        $insData['openId'] = $data['openId'];
        $insData['productId'] = $data['productId'];
        $insData['requestId'] = '';
        $insData['info'] = $request_body;
        $insData['merchant_id'] = $logData['data']['merchant_id'];
        $insData['remark'] = '实例销毁';
        $res = $tcsModel->add($insData);
        if ($res['status'] != 200) {
            file_put_contents(Yii::getAlias('@webroot/') . '/tencen_error.text', date('Y-m-d H:i:s') . 'DestroyInstance_res_' . json_encode($insData) . PHP_EOL, FILE_APPEND);
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['success' => 'true'];
    }

    /**
     * 检验signature
     * @param $signature
     * @param $timestamp
     * @param $eventId
     * @return bool
     */
    public static function checkSignature($signature, $timestamp, $eventId)
    {
        $currentTimestamp = time();
        if ($currentTimestamp - $timestamp > 30) {
            return false;
        }
        $timestamp = (string)$timestamp;
        $eventId = (string)$eventId;
        $params = array('3524F7DF539916687EB5F2B581F65F65', $timestamp, $eventId);
        sort($params, SORT_STRING);
        $str = implode('', $params);
        $requestSignature = hash('sha256', $str);
        if ($signature === $requestSignature) {
            return true;
        }
        return false;
    }
}
