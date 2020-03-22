<?php

/**
 * Created by 卷泡
 * author: wmy
 */

namespace app\controllers\pay;

use app\controllers\common\CommonController;
use app\models\merchant\vip\VipConfigModel;
use app\models\pay\WeixinModel;
use app\models\shop\UserModel;
use app\models\shop\VipAccessModel;
use app\models\shop\VoucherModel;
use app\models\shop\VoucherTypeModel;
use yii;
use yii\web\Controller;

require_once yii::getAlias('@vendor/wxpay/Wechat.php');

class VipAccessController extends Controller
{

    public $enableCsrfValidation = false; //禁用CSRF令牌验证，可以在基类中设置

    public function behaviors()
    {
        return [
            'token' => [
                'class' => 'yii\filters\TokenFilter', //调用过滤器
                'except' => ['notify', 'notify-sao-bei', 'voucher'], //指定控制器不应用到哪些动作
            ]
        ];
    }

    /**
     * 会员卡支付回调 线上环境
     * @throws \WxPayException
     */
    public function actionNotify()
    {
        $xml = file_get_contents("php://input");
        $wxPatNotify = new \WxPayNotify();
        $wxPatNotify->Handle(false);
        $result = $wxPatNotify->FromXml($xml);
        $tr = Yii::$app->db->beginTransaction();
        file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_wx.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
        if (!empty($result['result_code']) && $result['result_code'] == 'SUCCESS') {
            try {
                $vipAccessModel = new VipAccessModel();
                $vipAccessRs = $vipAccessModel->one(['pay_sn' => $result['out_trade_no']]);
                if ($vipAccessRs['status'] != 200) { // 若没有订单号
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_wx_no_id_error.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
                }
                if ($vipAccessRs['data']['status'] == 1) { // 若已经支付 直接返回
                    ob_clean();
                    echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                    die();
                }
                $res = $vipAccessModel->do_update(['pay_sn' => $result['out_trade_no']], ['status' => 1, 'transaction_id' => $result['transaction_id']]); // 改成已支付
                //修改shop_user 有效期
                $shopUserModel = new \app\models\shop\UserModel;
                $shopUser = $shopUserModel->find(['id' => $vipAccessRs['data']['user_id']]);
                if ($shopUser['status'] != 200) {
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_wx_no_user_error.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
                }
                if ((int)$shopUser['data']['vip_validity_time'] <= time()) { // 有效期小于等于当前时间
                    $vip_validity_time = bcadd(time(), (int)$vipAccessRs['data']['validity_time']);
                } else {
                    $vip_validity_time = bcadd((int)$shopUser['data']['vip_validity_time'], (int)$vipAccessRs['data']['validity_time']);
                }
                $res = $res && $shopUserModel->update(['id' => $vipAccessRs['data']['user_id'], '`key`' => $vipAccessRs['data']['key'], 'is_vip' => 1, 'vip_validity_time' => $vip_validity_time]);
                $result['wx_appid'] = $result['appid'];
                unset($result['appid']);
                $systemPayWeixin = new WeixinModel();
                $result['status'] = 1;
                $result['create_time'] = time();
                $res = $res && $systemPayWeixin->add($result);
                if ($res) {
                    $tr->commit();
                    ob_clean();
                    echo "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
                    die();
                } else {
                    $tr->rollBack();
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_error.text', date('Y-m-d H:i:s') . json_encode($result) . PHP_EOL, FILE_APPEND);
                    ob_clean();
                    echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[签名失败]]></return_msg></xml>';
                    die();
                }
            } catch (\Exception $e) {
                file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_exception.text', date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
        }
    }

    /**
     * 会员卡支付回调 小程序扫呗支付专用回调
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
        // 处理业务
        if (isset($data["result_code"]) && $data["result_code"] == "01") { //表示成功
            //处理业务 先记录条日志
            try {
                $tr = Yii::$app->db->beginTransaction();
                $vipAccessModel = new VipAccessModel();
                $vipAccessRs = $vipAccessModel->one(['pay_sn' => $data['terminal_trace']]);
                if ($vipAccessRs['status'] != 200) { // 若没有订单号
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_wx_no_id_error.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
                }
                if ($vipAccessRs['data']['status'] == 1) { // 若已经支付 直接返回
                    return result(200, ["return_code" => "01", "return_msg" => "success"]);
                }
                $res = $vipAccessModel->do_update(['pay_sn' => $data['terminal_trace']], ['status' => 1, 'transaction_id' => $data['out_trade_no']]); // 改成已支付
                //修改shop_user 有效期
                $shopUserModel = new \app\models\shop\UserModel;
                $shopUser = $shopUserModel->find(['id' => $vipAccessRs['data']['user_id']]);
                if ($shopUser['status'] != 200) {
                    file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_wx_no_user_error.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
                }
                if ((int)$shopUser['data']['vip_validity_time'] <= time()) { // 有效期小于等于当前时间
                    $vip_validity_time = bcadd(time(), (int)$vipAccessRs['data']['validity_time']);
                } else {
                    $vip_validity_time = bcadd((int)$shopUser['data']['vip_validity_time'], (int)$vipAccessRs['data']['validity_time']);
                }
                $res = $shopUserModel->update(['id' => $vipAccessRs['data']['user_id'], '`key`' => $vipAccessRs['data']['key'], 'is_vip' => 1, 'vip_validity_time' => $vip_validity_time]);
                if ($res) {
                    $tr->commit();
                    return result(200, ["return_code" => "01", "return_msg" => "success"]);
                }
                $tr->rollBack();
                return result(200, ["return_code" => "02", "return_msg" => "error"]);
            } catch (\Exception $e) {
                file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_exception.text', date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL, FILE_APPEND);
            }
        } else {
            // 错误处理 记录日志
            file_put_contents(Yii::getAlias('@webroot/') . '/pay_vip_error.text', date('Y-m-d H:i:s') . json_encode($data) . PHP_EOL, FILE_APPEND);
        }
    }

    /**
     * 购买会员卡赠送优惠券
     * @return bool
     */
    public function actionVoucher()
    {
        //检测是否是一号
        $time = date('Y-m-d', time());
        $key = $time . '_check_voucher';
        $arr = explode("-", $time);
        if($arr[2] != "01"){
          return true;
        }
        if (\yii::$app->redis->get($key)) {
            return true;
        };
        try {
            $userModel = new UserModel();
            $vipConfigModel = new VipConfigModel();
            $voucherTypeModel = new VoucherTypeModel();
            $voucherModel = new VoucherModel();
            $cc = new CommonController();
            $page = 1;
            $time = time();
            while (true) {
                $where["vip_validity_time >= {$time}"] = null;
                $where['is_vip'] = 1;
                $where['page'] = $page;
                $where['limit'] = 100;
                $list = $userModel->findall($where);
                if ($list['status'] == 200 && !empty($list['data'])) {
                    foreach ($list['data'] as $k => $val) {
                        $configInfo = $vipConfigModel->one(['key' => $val['key'], 'merchant_id' => $val['merchant_id']]);
                        if ($configInfo['status'] != 200) {
                            continue;
                        }
                        $info = $voucherTypeModel->find(['id' => $configInfo['data']['voucher_type_id']]);
                        if ($info['status'] != 200) {
                            continue;
                        }
                        //优惠券新增参数
                        if($configInfo['data']['voucher_count'] <= 0){
                            continue;
                        }
                        for ($i = 1; $i <= $configInfo['data']['voucher_count']; $i++) {
                            $vdata['cdkey'] = $cc->generateCode();
                            $vdata['type_id'] = $configInfo['data']['voucher_type_id'];
                            $vdata['type_name'] = $info['data']['name'];
                            $vdata['status'] = 1;
                            $vdata['start_time'] = time();
                            $vdata['end_time'] = $info['data']['to_date'];
                            $vdata['is_exchange'] = 0;
                            $vdata['merchant_id'] = $val['merchant_id'];
                            $vdata['`key`'] = $val['key'];
                            $vdata['is_used'] = 0;
                            $vdata['price'] = $info['data']['price'];
                            $vdata['full_price'] = $info['data']['full_price'];
                            $vdata['user_id'] = $val['id'];
                            $res = $voucherModel->add($vdata);
                            if ($res['status'] != 200) {
                                file_put_contents(Yii::getAlias('@webroot/') . '/voucher_error.text', date('Y-m-d H:i:s') . json_encode($vdata) . PHP_EOL, FILE_APPEND);
                                continue;
                            }
                        }
                        //更新优惠券个数
                        $typeparams['send_count'] = $info['data']['send_count'] + $configInfo['data']['voucher_count'];
                        $typeparams['id'] = $configInfo['data']['voucher_type_id'];
                        $res = $voucherTypeModel->update($typeparams);
                        if ($res['status'] == 200) {
                            continue;
                        } else {
                            file_put_contents(Yii::getAlias('@webroot/') . '/voucher_error.text', date('Y-m-d H:i:s') . json_encode($val) . PHP_EOL, FILE_APPEND);
                            continue;
                        }
                    }
                } else {
                    \yii::$app->redis->set($key,1);
                    return 3;
                }
                $page++;
            }
        } catch (\Exception $e) {
            file_put_contents(Yii::getAlias('@webroot/') . '/voucher_error.text', date('Y-m-d H:i:s') . $e->getMessage() . PHP_EOL, FILE_APPEND);
        }
    }

}
