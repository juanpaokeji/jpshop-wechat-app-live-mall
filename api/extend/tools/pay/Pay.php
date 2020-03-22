<?php

namespace tools\pay;


use tools\pay\js_pos_prepay\JsPosPrepayRe;
use tools\pay\pos_barcode\PosBarcodeRe;

include __DIR__ . DIRECTORY_SEPARATOR . 'Bootstrap.php';
include __DIR__ . DIRECTORY_SEPARATOR . 'MiniPay' . DIRECTORY_SEPARATOR . 'MiniPayRe.php';
include __DIR__ . DIRECTORY_SEPARATOR . 'Refund' . DIRECTORY_SEPARATOR . 'RefundRe.php';

class Payx
{
    const  JSPOS_PREPAY_URL = "https://pay.lcsw.cn/lcsw/pay/100/jspay"; //公众号预支付
    const  POS_BARCODE_PREPAY_URL = "https://pay.lcsw.cn/lcsw/lcsw/pay/100/jspay"; //刷卡预支付
    const  POS_PREPAY_URL = "https://pay.lcsw.cn/lcsw/pay/100/prepay"; //扫码预支付
    const  MINI_PREPAY_URL = "https://pay.lcsw.cn/lcsw/pay/100/minipay"; //小程序支付
    const  MINI_REFUND_URL = "https://pay.lcsw.cn/lcsw/pay/100/refund"; //退款
    const  PAY_VER = "100";   // 版本号
    const  SERVICE_ID = "012";   // 接口类型

    /**
     * 公众号支付
     * @param $jsposPrePay
     * @return jsposPrePayRe
     */
    public static function jsposPrePayRe($jsposPrePay)
    {
        if (!is_a($jsposPrePay, 'JsPosPrepay')) {
            //return new jsposPrePayRe();
            return new JsPosPrepayRe();
        }
        $jsonParam = array(
            "pay_ver" => $jsposPrePay->getPay_ver(),
            "pay_type" => $jsposPrePay->getPay_type(),
            "service_id" => $jsposPrePay->getService_id(),
            "merchant_no" => $jsposPrePay->getMerchant_no(),
            "terminal_id" => $jsposPrePay->getTerminal_id(),
            "terminal_trace" => $jsposPrePay->getTerminal_trace(),
            "terminal_time" => $jsposPrePay->getTerminal_time(),
            "total_fee" => $jsposPrePay->getTotal_fee(),
            "open_id" => $jsposPrePay->getOpen_id(),
            "order_body" => $jsposPrePay->getOrder_body(),
            "notify_url" => $jsposPrePay->getNotify_url(),
            "attach" => $jsposPrePay->getAttach()
        );
        $parm = "pay_ver=" . $jsposPrePay->getPay_ver() . "&pay_type=" . $jsposPrePay->getPay_type() . "&service_id="
            . $jsposPrePay->getService_id() . "&merchant_no=" . $jsposPrePay->getMerchant_no() . "&terminal_id="
            . $jsposPrePay->getTerminal_id() . "&terminal_trace=" . $jsposPrePay->getTerminal_trace()
            . "&terminal_time=" . $jsposPrePay->getTerminal_time() . "&total_fee=" . $jsposPrePay->getTotal_fee()
            // ."&order_body=".$jsposPrePay.getOrder_body()
            . "&access_token=" . self::ACCESS_TOKEN;
        $sign = md5($parm);
        $jsonParam['key_sign'] = $sign;
        return Payx::tojson(self::JSPOS_PREPAY_URL, json_encode($jsonParam));
    }

    /**
     * 刷卡（条码）支付
     * @param $posBarcode
     * @return posBarcodeRe
     */
    public static function posBarcodeRe($posBarcode)
    {
        if (!is_a($posBarcode, 'PosBarcode')) {
            return new PosBarcodeRe();
        }
        $jsonParam = array(
            "pay_ver" => $posBarcode->getPay_ver(),
            "pay_type" => $posBarcode->getPay_type(),
            "service_id" => $posBarcode->getService_id(),
            "merchant_no" => $posBarcode->getMerchant_no(),
            "terminal_id" => $posBarcode->getTerminal_id(),
            "terminal_trace" => $posBarcode->getTerminal_trace(),
            "terminal_time" => $posBarcode->getTerminal_time(),
            "auth_no" => $posBarcode->getAuth_no(),
            "total_fee" => $posBarcode->getTotal_fee()
        );
        $parm = "pay_ver=" . $posBarcode->getPay_ver() . "&pay_type=" . $posBarcode->getPay_type() . "&service_id="
            . $posBarcode->getService_id() . "&merchant_no=" . $posBarcode->getMerchant_no() . "&terminal_id="
            . $posBarcode->getTerminal_id() . "&terminal_trace=" . $posBarcode->getTerminal_trace()
            . "&terminal_time=" . $posBarcode->getTerminal_time() . "&auth_no=" . $posBarcode->getAuth_no() . "&total_fee=" . $posBarcode->getTotal_fee()
            // ."&order_body=".$posPrePay.getOrder_body()
            . "&access_token=" . self::ACCESS_TOKEN;
        $sign = md5($parm);
        $jsonParam['key_sign'] = $sign;
        return Payx::tojson(self::POS_BARCODE_PREPAY_URL, json_encode($jsonParam));
    }


    public static function posPrePayRe($posPrePay)
    {
        if (!is_a($posPrePay, 'PosPrepay')) {
            return new JsPosPrepayRe();
        }
        $jsonParam = array(
            "pay_ver" => $posPrePay->getPay_ver(),
            "pay_type" => $posPrePay->getPay_type(),
            "service_id" => $posPrePay->getService_id(),
            "merchant_no" => $posPrePay->getMerchant_no(),
            "terminal_id" => $posPrePay->getTerminal_id(),
            "terminal_trace" => $posPrePay->getTerminal_trace(),
            "terminal_time" => $posPrePay->getTerminal_time(),
            "total_fee" => $posPrePay->getTotal_fee(),
            "order_body" => $posPrePay->getOrder_body(),
            "notify_url" => $posPrePay->getNotify_url(),
            "attach" => $posPrePay->getAttach()
        );
        $parm = "pay_ver=" . $posPrePay->getPay_ver() . "&pay_type=" . $posPrePay->getPay_type() . "&service_id="
            . $posPrePay->getService_id() . "&merchant_no=" . $posPrePay->getMerchant_no() . "&terminal_id="
            . $posPrePay->getTerminal_id() . "&terminal_trace=" . $posPrePay->getTerminal_trace()
            . "&terminal_time=" . $posPrePay->getTerminal_time() . "&total_fee=" . $posPrePay->getTotal_fee()
            // ."&order_body=".$posPrePay.getOrder_body()
            . "&access_token=" . self::ACCESS_TOKEN;
        $sign = md5($parm);
        $jsonParam['key_sign'] = $sign;
        return Payx::tojson(self::POS_PREPAY_URL, json_encode($jsonParam));
    }

    /**
     * @param $miniPay
     * @param $access_token
     * @return mini_pay\RefundRe
     */
    public static function miniPayRe($miniPay, $access_token)
    {
        $jsonParam = array(
            "pay_ver" => $miniPay->getPay_ver(),
            "pay_type" => $miniPay->getPay_type(),
            "service_id" => $miniPay->getService_id(),
            "merchant_no" => $miniPay->getMerchant_no(),
            "terminal_id" => $miniPay->getTerminal_id(),
            "terminal_trace" => $miniPay->getTerminal_trace(),
            "terminal_time" => $miniPay->getTerminal_time(),
            "total_fee" => $miniPay->getTotal_fee(),
            "open_id" => $miniPay->getOpen_id(),
            "sub_appid" => $miniPay->getSub_appid(),
            "order_body" => $miniPay->getOrder_body(),
            "notify_url" => $miniPay->getNotify_url(),
            "attach" => $miniPay->getAttach()
        );
        $parm = "pay_ver=" . $miniPay->getPay_ver() . "&pay_type=" . $miniPay->getPay_type() . "&service_id="
            . $miniPay->getService_id() . "&merchant_no=" . $miniPay->getMerchant_no() . "&terminal_id="
            . $miniPay->getTerminal_id() . "&terminal_trace=" . $miniPay->getTerminal_trace()
            . "&terminal_time=" . $miniPay->getTerminal_time() . "&total_fee=" . $miniPay->getTotal_fee()
            . "&access_token=" . $access_token;
        $sign = md5($parm);
        $jsonParam['key_sign'] = $sign;
        return Payx::tojson(self::MINI_PREPAY_URL, json_encode($jsonParam));
    }

    public static function getOpenidFromMp($url)
    {
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        //取出openid
        $data = json_decode($res, true);
        //$openid['openid'] = $data;
        return $data;
    }

    public static function tojson($gateway, $jsonParam)
    {
        \tools\pay\Bootstrap::init();
        $xmlText = \tools\pay\Request::post($gateway)
            ->sendsJson()
            ->body($jsonParam)
            ->send();
        return $xmlText->body;
    }

    /**
     * @param $refund
     * @param $access_token
     * @return mixed
     */
    public static function refund($refund, $access_token)
    {
        $jsonParam = array(
            "pay_ver" => $refund->getPay_ver(),
            "pay_type" => $refund->getPay_type(),
            "service_id" => $refund->getService_id(),
            "merchant_no" => $refund->getMerchant_no(),
            "terminal_id" => $refund->getTerminal_id(),
            "terminal_trace" => $refund->getTerminal_trace(),
            "terminal_time" => $refund->getTerminal_time(),
            "refund_fee" => $refund->getRefund_fee(),
            "out_trade_no" => $refund->getOut_trade_no(),
        );
        $parm = "pay_ver=" . $refund->getPay_ver() . "&pay_type=" . $refund->getPay_type() . "&service_id="
            . $refund->getService_id() . "&merchant_no=" . $refund->getMerchant_no() . "&terminal_id="
            . $refund->getTerminal_id() . "&terminal_trace=" . $refund->getTerminal_trace()
            . "&terminal_time=" . $refund->getTerminal_time() . "&refund_fee=" . $refund->getRefund_fee()
            . "&out_trade_no=" . $refund->getOut_trade_no()
            . "&access_token=" . $access_token;
        $sign = md5($parm);
        $jsonParam['key_sign'] = $sign;
        return Payx::tojson(self::MINI_REFUND_URL, json_encode($jsonParam));
    }

}
