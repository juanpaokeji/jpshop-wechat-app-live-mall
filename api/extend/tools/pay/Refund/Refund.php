<?php

namespace tools\pay\refund;

/**
 * 退款实体
 * @author liuchu-zy
 *
 */
class Refund
{

    private $pay_ver;//版本号

    private $pay_type;//请求类型

    private $service_id;//接口类型

    private $merchant_no;//商户号

    private $terminal_id;//终端号

    private $terminal_trace;//终端流水号

    private $terminal_time;//终端交易时间

    private $refund_fee;//金额

    private $out_trade_no;//订单号，查询凭据，利楚订单号、微信订单号、支付宝订单号任意一个

    private $key_sign; //签名检验串


    public function getPay_ver()
    {
        return $this->pay_ver;
    }

    public function setPay_ver($pay_ver)
    {
        $this->pay_ver = $pay_ver;
    }

    public function getPay_type()
    {
        return $this->pay_type;
    }

    public function setPay_type($pay_type)
    {
        $this->pay_type = $pay_type;
    }

    public function getService_id()
    {
        return $this->service_id;
    }

    public function setService_id($service_id)
    {
        $this->service_id = $service_id;
    }

    public function getMerchant_no()
    {
        return $this->merchant_no;
    }

    public function setMerchant_no($merchant_no)
    {
        $this->merchant_no = $merchant_no;
    }

    public function getTerminal_id()
    {
        return $this->terminal_id;
    }

    public function setTerminal_id($terminal_id)
    {
        $this->terminal_id = $terminal_id;
    }

    public function getTerminal_trace()
    {
        return $this->terminal_trace;
    }

    public function setTerminal_trace($terminal_trace)
    {
        $this->terminal_trace = $terminal_trace;
    }

    public function getTerminal_time()
    {
        return $this->terminal_time;
    }

    public function setTerminal_time($terminal_time)
    {
        $this->terminal_time = $terminal_time;
    }

    public function getRefund_fee()
    {
        return $this->refund_fee;
    }

    public function setRefund_fee($refund_fee)
    {
        $this->refund_fee = $refund_fee;
    }


    public function getKey_sign()
    {
        return $this->key_sign;
    }

    public function setKey_sign($key_sign)
    {
        $this->key_sign = $key_sign;
    }

    public function getOut_trade_no()
    {
        return $this->out_trade_no;
    }

    public function setOut_trade_no($out_trade_no)
    {
        $this->out_trade_no = $out_trade_no;
    }

}
