<?php

/**
 * Created by 卷泡
 * author: wmy
 */

namespace app\controllers\pay;

use app\models\pay\WeixinModel;
use app\models\shop\BalanceAccessModel;
use yii;
use yii\web\Controller;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');

class BalanceAccessController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\TokenFilter', //调用过滤器
                'except' => ['notify', 'notify-sao-bei'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 余额充值支付回调 线上环境
     * @throws \WxPayException
     */
    public function actionNotify()
    {
        $xml = file_get_contents("php://input");
        $wxPatNotify = new \WxPayNotify();
        $wxPatNotify->Handle(false);
        $result = $wxPatNotify->FromXml($xml);
        file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_wx.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
        $tr = Yii::$app->db->beginTransaction();
        if (!empty($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            try {
                $balancepAccessModel = new BalanceAccessModel();
                $balanceAccessRs = $balancepAccessModel->one(['pay_sn' => $result['out_trade_no']]);
                if ($balanceAccessRs['status'] != 200) { // 若没有订单号
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_wx_no_id_error.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
                }
                if ($balanceAccessRs['data']['status'] == 1) { // 若已经支付 直接返回
                    ob_clean();
                    echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                    die();
                }
                $res = $balancepAccessModel->do_update(['pay_sn' => $result['out_trade_no']], ['status' => 1, 'transaction_id' => $result['transaction_id']]); // 改成已支付
                //修改shop_user 余额
                $shopUserModel = new \app\models\shop\UserModel;
                $shopUser = $shopUserModel->find(['id' => $balanceAccessRs['data']['user_id']]);
                if ($shopUser['status'] != 200) {
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_wx_no_user_error.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
                }
                $res = $res && $shopUserModel->update(['id' => $balanceAccessRs['data']['user_id'], '`key`' => $balanceAccessRs['data']['key'], 'recharge_balance' => bcadd($balanceAccessRs['data']['remain_money'],$shopUser['data']['recharge_balance'],2)]);
                $result['wx_appid'] = $result['appid'];
                unset($result['appid']);
                $systemPayWeixin = new WeixinModel();
                $result['status'] = 1;
                $result['create_time'] = time();
                $res = $res && $systemPayWeixin->add($result);
                // 新增一条banlance记录
                $balanceModel = new \app\models\shop\BalanceModel;
                $data_ba = array(
                    'uid' => $balanceAccessRs['data']['user_id'],
                    'order_sn' => $balanceAccessRs['data']['pay_sn'],
                    'money' => $balanceAccessRs['data']['remain_money'],
                    'content' => "充值余额",
                    'type' => 7,
                    'send_type' => 1,
                    'is_recharge_balance' => 1,
                    'status' => 1
                );
                $data_ba['key'] = $balanceAccessRs['data']['key'];
                $data_ba['merchant_id'] = $balanceAccessRs['data']['merchant_id'];
                $res = $res && $balanceModel->do_add($data_ba);
                if ($res) {
                    $tr->commit();
                    ob_clean();
                    echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                    die();
                } else {
                    $tr->rollBack();
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_error.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
                    ob_clean();
                    echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
                    die();
                }
            } catch (\Exception $e) {
                file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_exception.text', date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
        }
    }

    /**
     * 余额充值支付回调 小程序扫呗支付专用回调
     * @return array
     */
    public function actionNotifySaoBei()
    {
        //获取商户微信配置
        $request_body = file_get_contents('php://input');
        $data = json_decode($request_body, true);
        if (empty($data) || !is_array($data) || !isset($data['out_trade_no']) || empty($data['out_trade_no'])) {
            return result(200, ["return_code" => "01", "return_msg" => "缺少数据"]); //
        }
        file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
        // 处理业务
        if (isset($data["result_code"]) && $data["result_code"] == "01") { //表示成功
            try {
                $tr = Yii::$app->db->beginTransaction();
                $balanceAccessModel = new BalanceAccessModel();
                $balanceAccessRs = $balanceAccessModel->one(['pay_sn' => $data['terminal_trace']]);
                if ($balanceAccessRs['status'] != 200) { // 若没有订单号
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_wx_no_id_error.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
                }
                if ($balanceAccessRs['data']['status'] == 1) { // 若已经支付 直接返回
                    return result(200, ["return_code" => "01", "return_msg" => "success"]);
                }
                $res = $balanceAccessModel->do_update(['pay_sn' => $data['terminal_trace']], ['status' => 1, 'transaction_id' => $data['out_trade_no']]); // 改成已支付
                //修改shop_user余额
                $shopUserModel = new \app\models\shop\UserModel;
                $shopUser = $shopUserModel->find(['id' => $balanceAccessRs['data']['user_id']]);
                if ($shopUser['status'] != 200) {
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_wx_no_user_error.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
                }
                $res = $res && $shopUserModel->update(['id' => $balanceAccessRs['data']['user_id'], '`key`' => $balanceAccessRs['data']['key'], 'recharge_balance' => bcadd($balanceAccessRs['data']['remain_money'],$shopUser['data']['recharge_balance'],2)]);
                // 新增一条banlance记录
                $balanceModel = new \app\models\shop\BalanceModel;
                $data_ba = array(
                    'uid' => $balanceAccessRs['data']['user_id'],
                    'order_sn' => $balanceAccessRs['data']['pay_sn'],
                    'money' => $balanceAccessRs['data']['remain_money'],
                    'content' => "充值余额",
                    'type' => 7,
                    'send_type' => 1,
                    'is_recharge_balance' => 1,
                    'status' => 1
                );
                $data_ba['key'] = $balanceAccessRs['data']['key'];
                $data_ba['merchant_id'] = $balanceAccessRs['data']['merchant_id'];
                $res = $res && $balanceModel->do_add($data_ba);
                if ($res) {
                    $tr->commit();
                    return result(200, ["return_code" => "01", "return_msg" => "success"]);
                }
                $tr->rollBack();
                return result(200, ["return_code" => "02", "return_msg" => "error"]);
            } catch (\Exception $e) {
                file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_exception.text', date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
        } else {
            // 错误处理 记录日志
            file_put_contents(Yii::getAlias('@webroot/') . '/pay_balance_error.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
        }
    }

}
